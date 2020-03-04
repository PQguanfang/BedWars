<?php


namespace BedWars\command;


use BedWars\BedWars;
use BedWars\game\Game;
use BedWars\utils\Utils;
use pocketmine\block\Block;
use pocketmine\block\TNT;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\item\Bed;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;

class DefaultCommand extends PluginCommand
{

    const ARGUMENT_LIST = [
        'create' => "[gameName] [minPlayers] [playerPerTeam]",
        'addteam' => "[gameName] [teamName]",
        'delete' => "[gameName]",
        'setlobby' => "[gameName]",
        'setpos' => "[gameName] [team] [spawn,shop,upgrade]",
        'setbed' => "[gameName] [team]",
        'setgenerator' => "[gameName] [generator] [game=null]",
        'join' => "[gameName]"

    ];

    /**
     * DefaultCommand constructor.
     * @param BedWars $owner
     */
    public function __construct(BedWars $owner)
    {
        parent::__construct("bedwars", $owner);
        parent::setDescription("BedWars command");
        parent::setPermission("bedwars.command");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(empty($args[0])){
            $this->sendUsage($sender);
            return;
        }

        switch(strtolower($args[0])){
            case "list";
            $sender->sendMessage(TextFormat::BOLD . TextFormat::DARK_RED . "房间列表");
            foreach($this->getPlugin()->games as $game){
                $sender->sendMessage(TextFormat::GREEN . $game->getName());
                //todo: add other info
            }
            break;
            case "create";
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "这个指令只能在游戏中使用");
                return;
            }

            if(count($args) < 3){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . $this->generateSubCommandUsage($args[0]));
                return;
            }

            $gameName = $args[1];
            if(in_array($gameName, array_keys($this->getPlugin()->games))){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "被称为 " . $gameName . " 的房间已存在!");
                return;
            }

            if(!is_int(intval($args[2]))){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "minPlayers 值必须是整数!");
            }

            if(!is_int(intval($args[3]))){
                    $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "playersPerTeam 值必须是整数!");
            }

            $minPlayers = intval($args[2]);
            $maxPlayers = intval($args[3]);

            $world = $sender->level;

            $dataStructure = [
                'name' => $gameName,
                'minPlayers' => $minPlayers,
                'playersPerTeam' => $maxPlayers,
                'world' => $world->getFolderName(),
                'signs' => [],
                'teamInfo' => [],
                'generatorInfo' => []
            ];

            new Config($this->getPlugin()->getDataFolder() . "arenas/" . $gameName . ".json", Config::JSON, $dataStructure);
            $sender->sendMessage(BedWars::PREFIX . TextFormat::GREEN . "成功创建房间!");

            break;
            case "addteam";
            if(count($args) < 3){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . $this->generateSubCommandUsage($args[0]));
                return;
            }

            $gameName = $args[1];

            $location = $this->getPlugin()->getDataFolder() . "arenas/" . $gameName . ".json";
            if(!is_file($location)){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "房间不存在");
                   return;
            }

            $fileContent = file_get_contents($location);
            $jsonData = json_decode($fileContent, true);

            if(count($jsonData['teamInfo']) >= count(BedWars::TEAMS)){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "您的设置已超过正常游戏的每队人数，请更换一个更小的值!");
                return;
            }

            if(isset($jsonData['teamInfo'][$args[2]])){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "这个队伍已存在!");
                return;
            }

            $jsonData['teamInfo'][$args[2]] = ['spawnPos' => '', 'bedPos' => '', 'shopPos'];

            file_put_contents($location, json_encode($jsonData));
            $sender->sendMessage(BedWars::PREFIX . TextFormat::GREEN . "成功新增团队!");
            break;
            case "delete";
            if(count($args) < 2){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . $this->generateSubCommandUsage($args[0]));
                return;
            }

            $gameName = $args[1];
            if(!in_array($gameName, array_keys($this->getPlugin()->games))) {
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "被称为 " . $gameName . " 的房间不存在!");
                return;
            }

            //close the arena if it's running
            $gameObject = $this->getPlugin()->games[$gameName];
            if(!$gameObject instanceof Game){
                return; //wtf ??
            }

            $gameObject->stop();

            unlink($this->getPlugin()->getDataFolder() . "arenas/" . $gameName . ".json");
            $sender->sendMessage(BedWars::PREFIX . TextFormat::GREEN . "房间已被成功删除!");

            break;
            case "setlobby";
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "这个指令只能在游戏中使用");
                return;
            }

            if(count($args) < 2){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . $this->generateSubCommandUsage($args[0]));
                return;
            }

            $gameName = $args[1];

            $location = $this->getPlugin()->getDataFolder() . "arenas/" . $gameName . ".json";
            if(!is_file($location)){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "这个房间不存在");
                return;
            }

            $level = $sender->level;
            $void_y = Level::Y_MAX;
            foreach ($level->getChunks() as $chunk) {
                for ($x = 0; $x < 16; ++$x) {
                    for ($z = 0; $z < 16; ++$z) {
                        for ($y = 0; $y < $void_y; ++$y) {
                            $block = $chunk->getBlockId($x, $y, $z);
                            if ($block !== Block::AIR) {
                                $void_y = $y;
                                break;
                            }
                        }
                    }
                }
            }
            --$void_y;

            $fileContent = file_get_contents($location);
            $jsonData = json_decode($fileContent, true);
            $positionData = [
                'lobby' => $sender->getX() . ":" . $sender->getY() . ":" . $sender->getZ() . ":" . $sender->level->getFolderName(),
                'void_y' => $void_y
            ];

            file_put_contents($location, json_encode(array_merge($jsonData, $positionData)));
            break;
            case "setpos";
            if(count($args) < 3){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . $this->generateSubCommandUsage($args[0]));
                return;
            }

            $gameName = $args[1];
            $location = $this->getPlugin()->getDataFolder() . "arenas/" . $gameName . ".json";
            if(!is_file($location)){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "这个房间不存在");
                return;
            }

            $fileContent = file_get_contents($location);
            $jsonData = json_decode($fileContent, true);


            $teamName = $args[2];
            if(!isset($jsonData['teamInfo'][$args[2]])){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "这个队伍不存在!");
                return;
            }

            if(!in_array(strtolower($args[3]), array('spawn', 'shop', 'upgrade'))){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "无效值");
                return;
            }

            $jsonData['teamInfo'][$teamName][strtolower($args[3]) . "Pos"] = $sender->getX() . ":" . $sender->getY() . ":" . $sender->getZ() . ":" . $sender->getYaw() . ":" . $sender->getPitch();

            file_put_contents($location, json_encode($jsonData));
            $sender->sendMessage(\BedWars\BedWars::PREFIX . TextFormat::GREEN . "成功更新");
            break;
            case "setbed";
            if(!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "这个指令只能在游戏中使用");
                return;
            }

            if(count($args) < 2){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . $this->generateSubCommandUsage($args[0]));
                return;
            }

            $gameName = $args[1];
            $location = $this->getPlugin()->getDataFolder() . "arenas/" . $gameName . ".json";
            if(!is_file($location)){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "这个房间不存在");
                return;
            }

            $fileContent = file_get_contents($location);
            $jsonData = json_decode($fileContent, true);

            $teamName = $args[2];
            if(!isset($jsonData['teamInfo'][$args[2]])){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "这个队伍不存在!");
                return;
            }

            $this->getPlugin()->bedSetup[$sender->getRawUniqueId()] = ['game' => $gameName, 'team' => $teamName , 'step' => 1];
            $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "请通过破坏床来选择队伍床");
            break;
            case "setgenerator";
            if(!$sender instanceof Player){
                $sender->sendMessage(TextFormat::RED . "这个指令只能在游戏中使用");
                return;
            }

            if(count($args) < 3){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . $this->generateSubCommandUsage($args[0]));
                return;
            }

            $gameName = $args[1];
            if(!$this->getPlugin()->gameExists($gameName)){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "这个房间不存在");
                return;
            }

            $generatorType = $args[2];
            if(!in_array($generatorType, array('iron', 'gold', 'emerald', 'diamond'))){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "产矿机: " . TextFormat::RED . "iron,gold,diamond,emerald");
                return;
            }

            $arenaData = $this->getPlugin()->getArenaData($gameName);
            $arenaData['generatorInfo'][$gameName][] = ['type' => $generatorType, 'position' => Utils::vectorToString("", $sender), 'game'];
            $this->getPlugin()->writeArenaData($gameName, $arenaData);

            $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "成功新建产矿机 " . TextFormat::GREEN . "[game=" . $gameName . " | type=" . $generatorType . "]");
            break;
            case "best";
            if(!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "这个指令只能在游戏中使用");
                return;
            }

            $games = array_values($this->getPlugin()->arenas);
            $best = $games[0];
            foreach($games as $game){
                if(count($game->players) >= count($best->players)){
                    $best = $game;
                }
            }

            $best->join($sender);
            break;

            case "join";
            if(!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "这个指令只能在游戏中使用");
                return;
            }

            if(!isset($args[1])){
               $this->generateSubCommandUsage($args[0]);
               return;
            }
            $gameName = $args[1];

            if(!isset($this->getPlugin()->arenas[$gameName])){
                $sender->sendMessage(BedWars::PREFIX . TextFormat::YELLOW . "这个房间不存在");
                return;
            }

            $this->getPlugin()->arenas[$gameName]->join($sender);
            break;
            case "quit";
            if(!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "这个指令只能在游戏中使用");
                return;
            }

            $playerGame = $this->getPlugin()->getPlayerGame($sender);
            if($playerGame == null)return;

            $playerGame->quit($sender);
            $sender->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());


            break;
        }
    }

    /**
     * @param CommandSender $sender
     */
    private function sendUsage(CommandSender $sender) : void{
        $sender->sendMessage(TextFormat::BOLD . TextFormat::DARK_RED . "BedWars Commands");
        $sender->sendMessage(TextFormat::GREEN . "/bedwars list " . TextFormat::YELLOW . "展现所有已加载的房间");
        $sender->sendMessage(TextFormat::GREEN . "/bedwars create " . TextFormat::YELLOW . "新建一个房间");
        $sender->sendMessage(TextFormat::GREEN . "/bedwars delete " . TextFormat::YELLOW . "删除一个已存在的房间");
        $sender->sendMessage(TextFormat::GREEN . "/bedwars setlobby " . TextFormat::YELLOW . "设置一个房间的出生点");
        $sender->sendMessage(TextFormat::GREEN . "/bedwars setpos " . TextFormat::YELLOW . "设置一个队伍的[出生点|商店|升级商店] [spawn,shop,upgrade]");
        $sender->sendMessage(TextFormat::GREEN . "/bedwars setbed ". TextFormat::YELLOW . "设置一个床的位置");
        $sender->sendMessage(TextFormat::GREEN . "/bedwars setgenerator " . TextFormat::YELLOW . "设置一个产矿机");
    }

    /**
     * @param string $subCommand
     * @return string
     */
    public function generateSubCommandUsage(string $subCommand) : string
    {
         $args = self::ARGUMENT_LIST[$subCommand];
         return "/bedwars " . $subCommand . " " . $args;
    }

}
