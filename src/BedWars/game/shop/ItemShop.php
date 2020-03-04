<?php


namespace BedWars\game\shop;


use BedWars\BedWars;
use BedWars\game\Game;
use BedWars\utils\Utils;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;

class ItemShop
{

    const PURCHASE_TYPE_IRON = 0;
    const PURCHASE_TYPE_GOLD = 1;
    const PURCHASE_TYPE_EMERALD = 2;

    /**
     * @var array $shopWindows
     */
    public static $shopWindows = [
        1 => ["name" => "§l§a盔甲", "image" => "https://www.spigotmc.org/attachments/ftiroac-png.241966/"],
        2 => ["name" => "§l§a武器", "image" => "http://icons.iconarchive.com/icons/chrisl21/minecraft/512/Stone-Sword-icon.png"],
        3 => ["name" => "§l§a方块", "image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/0/07/White_Wool.png"],
        4 => ["name" => "§l§a弓", "image" => "https://lh3.googleusercontent.com/MS2w4Tlmw4azfmxVcT9o29cq74YgMau26xni5DiqbzpxHFMIEpHtPxdGWWZlV-WD0oXNLUXsiKs2W2yAzMT0=s400"],
        5 => ["name" => "§l§a药水" ,"image" => "http://www.blocksandgold.com//media/catalog/product/cache/3/image/200x/6cffa5908a86143a54dc6ad9b8a7c38e/s/p/splash_red.png"],
        6 => ["name" => "§l§a道具", "image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/1/1e/TNT.png"]
    ];

    /**
     * @var array $shopPages
     */
    public static $shopPages = [
        0 => ["§6锁链甲\n§l§e40 铁" => ["image" => "https://www.spigotmc.org/attachments/ftiroac-png.241966/"],
            "§6铁甲 §c[PERMANENT]\n§l§e12 金" => ["image" => "http://www.minecraftopia.com/images/blocks/iron_boots.png"],
            "§6钻石甲 §c[PERMANENT]\n§l§e6 绿宝石" => ["http://static.wixstatic.com/media/34c645_d8e567b9ae8645fa98e0775d45493df3.png"]],
        1 => ["§6石剑\n§l§e10 铁" => ["image" => "http://iconbug.com/data/0c/256/b86a957742c00c3804bd0fa293c98cde.png"],
            "§6铁剑\n§l§e7 金" => ["image" => "https://vignette.wikia.nocookie.net/animaljam/images/2/27/Iron-Sword-icon.png/revision/latest/scale-to-width-down/480?cb=20141108192633"],
            "§6钻石剑\n§l§e7 绿宝石" => ["image" => "http://www.freepngimg.com/download/minecraft/16-2-diamond-sword-minecraft-png.png"],
            "§6击退棒\n§l§e2 绿宝石" => ["image" => "https://vignette.wikia.nocookie.net/thetekkit/images/c/c4/Obsidian_Stick.png/revision/latest?cb=20121011074642"]],
        2 => ["§6羊毛 16x\n§l§e4 铁" => ["image" => "http://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/0/07/White_Wool.png"],
            "§6沙石 16x\n§l§e12 铁" => ["image" => "http://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/d/d6/Sandstone.png"],
            "§6末地石 12x\n§l§e24 铁" => ["image" => "http://www.minecraftguides.org/blocks/endstone.png"],
            "§6梯子 16x\n§l§e4 铁" => ["image" => "https://hydra-media.cursecdn.com/minecraft.gamepedia.com/archive/6/63/20101021024334!Ladder.png"],
            "§6橡木 16x\n§l§e4 金" => ["image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/0/0f/Oak_Wood_Planks.png"],
            "§6黑曜石 4x\n§l§e4 绿宝石" => ["image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/2/23/Obsidian.png"]],
        3 => ["§6普通弓\n§l§e12 金" => ["image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/6/65/Bow.png"],
            "§6弓 §b(力量 1)\n§l§e24 金" => ["image" => "https://i.imgur.com/MWw3yIJ.png"],
            "§6弓 §b(力量 1, 击退 1)\n§l§e6 绿宝石" => ["image" => "https://i.imgur.com/MWw3yIJ.png"]],
        4 => ["§6速度 II 药水 (45 秒)\n§l§e1 绿宝石" => ["image" => "http://www.blocksandgold.com//media/catalog/product/cache/3/image/200x/6cffa5908a86143a54dc6ad9b8a7c38e/p/o/potion_light_blue.png"],
            "§6跳跃提升 V 药水 (45 秒)\n§l§e1 绿宝石" => ["http://www.minecraftopia.com/images/blocks/potion_of_leaping.png"],
            "§6隐身药水 (30 秒)\n§l§e1 绿宝石" => ["https://vignette.wikia.nocookie.net/minecraft-computer/images/7/78/Potion_blue.png/revision/latest?cb=20130701150754"]],
        5 => ["§6金苹果\n§l§e3 金" => ["image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/0/0e/Golden_Apple.png"],
            "§6生成 蠹虫\n§l§e50 铁" => ["image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/0/04/Snowball.png"],
            "§6火球\n§l§e50 铁" => ["image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/9/98/Fire_Charge.png"],
            "§6TNT\n§l§e8 金" => ["image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/1/1e/TNT.png"],
            "§6末影珍珠\n§l§e4 绿宝石" => ["image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/5/5a/Ender_Pearl.png"],
            "§6Water Bucker\n§l§e1 绿宝石" => ["image" => "https://vignette.wikia.nocookie.net/minecraftpocketedition/images/d/d3/Water_Bucket.png/revision/latest/fixed-aspect-ratio-down/width/320/height/320?cb=20141004050736&fill=transparent"],
            "§6Egg\n§l§e4 绿宝石" => ["image" => "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/2/26/Egg.png"]
        ],
    ];

    /**
     * @var array $itemData
     */
    public static $itemData = [
        0 => [0 => ["name" => "锁链甲", "type" => self::PURCHASE_TYPE_IRON, "amount" => 0, "price" => 40, "item" => ["id" => Item::CHAIN_LEGGINGS, "damage" => 0]],
            1 => ["name" => "铁甲", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 0, "price" => 12, "item" => ["id" => Item::IRON_LEGGINGS, "damage" => 0]],
            2 => ["name" => "钻石甲", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 0, "price" => 6, "item" => ["id" => ITEM::DIAMOND_LEGGINGS, "damage" => 0]]
        ],
        1 => [0 => ["name" => "石剑", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" => 10, "item" => ["id" => Item::STONE_SWORD, "damage" => 0]],
            1 => ["name" => "铁剑", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 7, "item" => ["id" => Item::IRON_SWORD, "damage" => 0]],
            2 => ["name" => "钻石剑", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 7, "item" => ["id" => Item::DIAMOND_SWORD, "damage" => 0]],
            3 => ["name" => "击退棒", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 2, "item" => ["id" => Item::STICK, "damage" => 0]]
        ],
        2 => [0 => ["name" => "羊毛 16x", "type" => self::PURCHASE_TYPE_IRON, "amount" => 16, "price" => 4, "item" => ["id" => Item::WOOL, "damage" => "depend"]],
            1 => ["name" => "沙石 16x", "type" => self::PURCHASE_TYPE_IRON, "amount" => 16, "price" => 12, "item" => ["id" => Item::SANDSTONE, "damage" => 0]],
            2 => ["name" => "末地石 12x", "type" => self::PURCHASE_TYPE_IRON, "amount" => 12, "price" => 24,"item" => ["id" => Item::END_STONE, "damage" => 0]],
            3 => ["name" => "梯子 16x", "type" => self::PURCHASE_TYPE_IRON, "amount" => 16, "price" => 4,"item" => ["id" => Item::LADDER, "damage" => 0]],
            4 => ["name" => "橡木 16x", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 16, "price" => 4, "item" => ["id" => 5, "damage" => 0]],
            5 => ["name" => "黑曜石 4x", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 4, "price" => 4, "item" => ["id" => Item::OBSIDIAN, "damage" =>0]]
        ],
        3 => [0 => ["name" => "弓 1", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 12, "item" => ["id" => Item::BOW, "damage" => 0]],
            1 => ["name" => "弓 2", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 24, "item" => ["id" => Item::BOW, "damage" => 0]],
            2 => ["name" => "弓 3", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 6, "item" => ["id" => Item::BOW, "damage" => 0]]
        ],
        4 => [0 => ["name" => "Potion of Speed", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 1, "item" => ["id" => Item::POTION, "damage" => 8194]],
            1 => ["name" => "Jump Potion", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 1, "item" => ["id" => Item::POTION, "damage" => 8203]],
            2 => ["name" => "Invisibility Potion", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 1, "item" => ["id" => Item::POTION, "damage" => 8206]]
        ],
        5 => [0 => ["name" => "Golden Apple", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 3, "item" => ["id" => Item::GOLDEN_APPLE, "damage" => 0]],
            1 => ["name" => "Bedbug", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" => 50, "item" => ["id" => Item::SNOWBALL, "damage" => 0]],
            2 => ["name" => "Fireball", "type" => self::PURCHASE_TYPE_IRON, "amount" => 1, "price" => 50, "item" => ["id" => Item::FIRE_CHARGE, "damage" => 0]],
            3 => ["name" => "TNT", "type" => self::PURCHASE_TYPE_GOLD, "amount" => 1, "price" => 8, "item" => ["id" => Item::TNT, "damage" => 0]],
            4 => ["name" => "Enderpearl", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 4, "item" => ["id" => Item::ENDER_PEARL, "damage" => 0]],
            5 => ["name" => "Water Bucket", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 1, "item" => ["id" => 326, "damage" => 0]],
            6 => ["name" => "Egg", "type" => self::PURCHASE_TYPE_EMERALD, "amount" => 1, "price" => 4, "item" => ["id" => Item::EGG, "damage" => 0]]
        ]
    ];

    /**
     * @param int $category
     * @return mixed
     */
    public static function getCategory(int $category){
        return self::$shopWindows[$category];
    }

    /**
     * @param int $id
     * @param $data
     * @param Player $p
     * @param BedWars $plugin
     */
    public static function handleTransaction(int $id, $data, Player $p, BedWars $plugin){
        if(is_null($data)){
            return;
        }
        $itemData = self::$itemData[$id][$data];
        $amount = $itemData['amount'];
        $price = $itemData['price'];
        $id = $itemData['item']['id'];
        $damage = (int)$itemData['item']['damage'];
        $p->sendMessage($itemData["amount"] . " & " . $itemData["price"]);
        $check = "";
        $type = $itemData['type'];
        $typeString = "";
        $removeItem = null;
        switch($type){
            case self::PURCHASE_TYPE_IRON;
                $typeString = "iron";
                $removeItem = Item::get(Item::IRON_INGOT, 0, $price);
                $check = $p->getInventory()->contains(Item::get(Item::IRON_INGOT, $damage, $price));
                break;
            case self::PURCHASE_TYPE_GOLD;
                $typeString = "gold";
                $removeItem = Item::get(Item::GOLD_INGOT, 0 , $price);
                $check = $p->getInventory()->contains(Item::get(Item::GOLD_INGOT, $damage, $price));
                break;
            case self::PURCHASE_TYPE_EMERALD;
                $typeString = "emerald";
                $removeItem = Item::get(Item::EMERALD, 0, $price);
                $check = $p->getInventory()->contains(Item::get(Item::EMERALD, $damage, $price));
                break;
        }

        if(!$check){
            $p->sendMessage("§cYou don't have enough " . strtolower(ucfirst($typeString)) . " to purchase this item!");
            return;
        }

        $playerTeam = $plugin->getPlayerTeam($p);
        if($playerTeam == null)return;


        if($id == Item::WOOL){
            $damage = Utils::colorIntoWool($playerTeam->getColor());
        }elseif(Item::get($id) instanceof Armor){
            self::handleArmorTransaction($data, $p);
            return;
        }
        $item = Item::get($id, $damage, $amount);
        $wasPurchased = false;

        //handle custom sword transactions
        foreach($p->getInventory()->getContents() as $index => $content){
            if(self::isSword($content->getId()) && self::isSword($id)) {
                $wasPurchased = true;
                if ($id !== $content->getId()) {
                    $p->getInventory()->removeItem($content);
                    $p->getInventory()->setItem($index, $item);
                }else{
                    $p->sendMessage("§cYou already have this sword!");
                    return;
                }
            }
        }
        $p->sendMessage("§aYou have sucesfully purchased §e" . $itemData['name'] . " §afor §e" . $price . " " .  ucfirst($typeString));
        if($wasPurchased){
            return;
        }

        if($id == Item::BOW){
            self::handleBowTransaction($data, $item);
        }

        $p->getInventory()->removeItem($removeItem);
        $p->getInventory()->addItem($item);
    }

    /**
     * @param int $data
     * @param Player $p
     */
    public static function handleArmorTransaction(int $data, Player $p){
        $data = intval($data);
        $boots = "";
        $leggings = "";
        switch ($data){
            case 0;
                $boots = Item::get(Item::CHAIN_BOOTS, 0, 1);
                $leggings = Item::get(Item::CHAIN_LEGGINGS, 0, 1);
                break;
            case 1;
                $boots = Item::get(Item::IRON_BOOTS);
                $leggings = Item::get(Item::IRON_LEGGINGS);
                break;
            case 2;
                $boots = Item::get(Item::DIAMOND_BOOTS);
                $leggings = Item::get(Item::DIAMOND_LEGGINGS);
        }
        $p->getArmorInventory()->setBoots($boots);
        $p->getArmorInventory()->setLeggings($leggings);
    }

    /**
     * @param int $data
     * @param Item $item
     */
    public static function handleBowTransaction(int $data, Item $item){
        switch ($data){
            case 1;
                $enchantment = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::POWER), 1);
                $item->addEnchantment($enchantment);
                break;
            case 2;
                $enchantment = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::POWER), 1);
                $enchantment1 = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PUNCH), 1);
                foreach([$enchantment, $enchantment1] as $eIns){
                     $item->addEnchantment($eIns);
                }
        }
    }

    /**
     * @param int $itemId
     * @return bool
     */
    public static function isSword(int $itemId){
        $swords = [Item::IRON_SWORD, Item::STONE_SWORD, Item::WOODEN_SWORD, Item::DIAMOND_SWORD];
        if(in_array($itemId, $swords)){
            return true;
        }
        return false;
    }

    /**
     * @param int $itemId
     * @return bool
     */
    public static function isArmor(int $itemId){
        $armors = [Item::CHAIN_BOOTS, Item::CHAIN_BOOTS, Item::IRON_BOOTS, Item::IRON_LEGGINGS, Item::DIAMOND_BOOTS, Item::DIAMOND_LEGGINGS];
        if(in_array($itemId, $armors)){
            return true;
        }
        return false;
    }

    /**
     * @param Player $p
     */
    public static function sendDefaultShop(Player $p){
        $data['title'] = "Item Shop";
        $data['type'] = "form";
        $data['content'] = "";
        foreach(self::$shopWindows as $windows){
            $button =  ['text' => $windows['name']];
            $button['image']['type'] = "url";
            $button['image']['data'] = $windows['image'];
            $data['buttons'][] = $button;
        }

        $packet = new ModalFormRequestPacket();
        $packet->formId = 50;
        $packet->formData = json_encode($data);
        $p->dataPacket($packet);

    }


    /**
     * @param Player $p
     * @param int $page
     */
    public static function sendPage(Player $p, int $page){
        $formId = $page;
        $data['title'] = 'Page ' . $page;
        $data['type'] = 'form';
        $page = self::$shopPages[$page];
        $data['content'] = "";
        foreach($page as $itemsToBuy => $key){
            $string = strval($itemsToBuy);
            $button = ['text' => $string];
            if(!empty($key['image'])){
                $button['image']['type'] = 'url';
                $button['image']['data'] = $key['image'];
            }
            $data['buttons'][] = $button;
        }

        $packet = new ModalFormRequestPacket();
        $packet->formId = $formId;
        $packet->formData = json_encode($data);
        $p->dataPacket($packet);

    }



}
