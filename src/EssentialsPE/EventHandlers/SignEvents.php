<?php

namespace EssentialsPE\EventHandlers;

use EssentialsPE\Loader;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;

class SignEvents implements Listener{
    /** @var Loader */
    public $plugin;
    
    public function __construct(Loader $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onSignTap(PlayerInteractEvent $event){
        $tile = $event->getBlock()->getLevel()->getTile(new Vector3($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ()));
        if($tile instanceof Sign){
            // Free sign
            // TODO Implement costs
            if($tile->getText()[0] === "[Free]"){
                $event->setCancelled(true);
                if(!$event->getPlayer()->hasPermission("essentials.sign.use.free")){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to use this sign");
               }else{
                    if($event->getPlayer()->getGamemode() === 1 || $event->getPlayer()->getGamemode() === 3){
                        $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] You're in " . $event->getPlayer()->getServer()->getGamemodeString($event->getPlayer()->getGamemode()) . " mode");
                        return;
                    }

                    $item_name = $tile->getText()[1];
                    $damage = $tile->getText()[2];

                    $item = $this->plugin->getItem($item_name . ":" . $damage);

                    $event->getPlayer()->getInventory()->addItem($item);
                    $event->getPlayer()->sendMessage(TextFormat::YELLOW . "Giving " . TextFormat::RED . $item->getCount() . TextFormat::YELLOW . " of " . TextFormat::RED . ($item->getName() === "Unknown" ? $item_name : $item->getName()));
                }
            }

            // Gamemode sign
            // TODO Implement costs
            elseif($tile->getText()[0] === "[Gamemode]"){
                $event->setCancelled(true);
                if(!$event->getPlayer()->hasPermission("essentials.sign.use.gamemode")){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to use this sign");
               }else{
                    $v = strtolower($tile->getText()[1]);
                    if($v === "survival"){
                        $event->getPlayer()->setGamemode(0);
                    }elseif($v === "creative"){
                        $event->getPlayer()->setGamemode(1);
                    }elseif($v === "adventure"){
                        $event->getPlayer()->setGamemode(2);
                    }elseif($v === "spectator"){
                        $event->getPlayer()->setGamemode(3);
                    }
                }
            }

            // Heal sign
            // TODO Implement costs
            elseif($tile->getText()[0] === "[Heal]"){
                $event->setCancelled(true);
                if(!$event->getPlayer()->hasPermission("essentials.sign.use.heal")){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to use this sign");
                }elseif($event->getPlayer()->getGamemode() === 1 || $event->getPlayer()->getGamemode() === 3){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] You're in " . $event->getPlayer()->getServer()->getGamemodeString($event->getPlayer()->getGamemode()) . " mode");
                    return;
               }else{
                    $event->getPlayer()->heal($event->getPlayer()->getMaxHealth());
                    $event->getPlayer()->sendMessage(TextFormat::GREEN . "You have been healed!");
                }
            }
            
            // Kit sign
            // TODO: Implement costs
            elseif($tile->getText()[0] === "[Kit]"){
                $event->setCancelled(true);
                if(!$event->getPlayer()->hasPermission("essentials.sign.use.kit")){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to use this sign");
                }elseif($event->getPlayer()->getGamemode() === 1 || $event->getPlayer()->getGamemode() === 3){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] You're in " . $event->getPlayer()->getServer()->getGamemodeString($event->getPlayer()->getGamemode()) . " mode");
                    return;
                }else{
                    if(!$event->getPlayer()->hasPermission("essentials.kits." . strtolower($tile->getText()[1]))){
                        $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] You don't have permissions to get this kit");
                        return;
                    }elseif(!$this->plugin->getKit($tile->getText()[1])){
                        $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Kit doesn't exists");
                        return;
                    }else{
                        foreach($tile->getText()[1] as $k){
                            $k = explode(" ", $k);
                            if(count($k) > 1){
                                $amount = $k[1];
                            }else{
                                $amount = 1;
                            }
                            $item_name = $k[0];
                            $item = $this->plugin->getItem($item_name);
                            if($item->getID() === 0) {
                                return;
                            }
                            $item->setCount($amount);
                            $event->getPlayer()->getInventory()->setItem($event->getPlayer()->getInventory()->firstEmpty(), $item);
                        }
                        $event->getPlayer()->sendMessage(TextFormat::GREEN . "Getting kit " . $tile->getText()[1] . "...");
                    }
                }
            }

            // Repair sign
            // TODO Implement costs
            elseif($tile->getText()[0] === "[Repair]"){
                $event->setCancelled(true);
                if(!$event->getPlayer()->hasPermission("essentials.sign.use.repair")){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to use this sign");
                }elseif($event->getPlayer()->getGamemode() === 1 || $event->getPlayer()->getGamemode() === 3){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] You're in " . $event->getPlayer()->getServer()->getGamemodeString($event->getPlayer()->getGamemode()) . " mode");
                    return;
               }else{
                    if(($v = $tile->getText()[1]) === "Hand"){
                        if($this->plugin->isReparable($item = $event->getPlayer()->getInventory()->getItemInHand())){
                            $item->setDamage(0);
                            $event->getPlayer()->sendMessage(TextFormat::GREEN . "Item successfully repaired!");
                        }
                    }elseif($v === "All"){
                        foreach ($event->getPlayer()->getInventory()->getContents() as $item){
                            if($this->plugin->isReparable($item)){
                                $item->setDamage(0);
                            }
                        }
                        foreach ($event->getPlayer()->getInventory()->getArmorContents() as $item){
                            if($this->plugin->isReparable($item)){
                                $item->setDamage(0);
                            }
                        }
                        $event->getPlayer()->sendMessage(TextFormat::GREEN . "All the tools on your inventory were repaired!" . TextFormat::AQUA . "\n(including the equipped Armor)");
                    }
                }
            }

            // Time sign
            // TODO Implement costs
            elseif($tile->getText()[0] === "[Time]"){
                $event->setCancelled(true);
                if(!$event->getPlayer()->hasPermission("essentials.sign.use.time")){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to use this sign");
               }else{
                    if(($v = $tile->getText()[1]) === "Day"){
                        $event->getPlayer()->getLevel()->setTime(0);
                        $event->getPlayer()->sendMessage(TextFormat::GREEN . "Time set to \"Day\"");
                    }elseif($v === "Night"){
                        $event->getPlayer()->getLevel()->setTime(12500);
                        $event->getPlayer()->sendMessage(TextFormat::GREEN . "Time set to \"Night\"");
                    }
                }
            }

            // Teleport sign
            // TODO Implement costs
            elseif($tile->getText()[0] === "[Teleport]"){
                $event->setCancelled(true);
                if(!$event->getPlayer()->hasPermission("essentials.sign.use.teleport")){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to use this sign");
               }else{
                    $event->getPlayer()->teleport(new Vector3($x = $tile->getText()[1], $y = $tile->getText()[2], $z = $tile->getText()[3]));
                    $event->getPlayer()->sendMessage(TextFormat::GREEN . "Teleporting to " . TextFormat::AQUA . $x . TextFormat::GREEN . ", " . TextFormat::AQUA . $y . TextFormat::GREEN . ", " . TextFormat::AQUA . $z);
                }
            }

            // Warp sign
            // TODO Implement costs
            elseif($tile->getText()[0] === "[Warp]"){
                $event->setCancelled(true);
                if(!$event->getPlayer()->hasPermission("essentials.sign.use.warp")){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to use this sign");
               }else{
                    $warp = $this->plugin->getWarp($tile->getText()[1]);
                    if(!$warp){
                        $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Warp doesn't exists");
                        return;
                    }
                    if(!$event->getPlayer()->hasPermission("essentials.warps.*") && !$event->getPlayer()->hasPermission("essentials.warps." . $tile->getText()[1])){
                        $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] You can't teleport to that warp");
                        return;
                    }
                    $event->getPlayer()->teleport($warp[0], $warp[1], $warp[2]);
                    $event->getPlayer()->sendMessage(TextFormat::GREEN . "Warping to " . $tile->getText()[1] . "...");
                }
            }

            /**
             * Economy signs
             */

            // Balance sign
            /**elseif($tile->getText()[0] === "[Balance]"){
             * $event->setCancelled(true);
             * if(!$event->getPlayer()->hasPermission("essentials.sign.use.balance")){
             * $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to use this sign");
             * }else{
             * $event->getPlayer()->sendMessage(TextFormat::AQUA . "Your current balance is " . TextFormat::YELLOW . $this->plugin->getCurrencySymbol() . $this->plugin->getPlayerBalance($event->getPlayer()));
             * }
             * }*/

            /**
             * TODO Implement:
             * - Buy sign
             * - Sell sign
             */
        }
    }

    /**
     * @param BlockBreakEvent $event
     *
     * @priority HIGH
     */
    public function onBlockBreak(BlockBreakEvent $event){
        $tile = $event->getBlock()->getLevel()->getTile(new Vector3($event->getBlock()->getFloorX(), $event->getBlock()->getFloorY(), $event->getBlock()->getFloorZ()));
        if($tile instanceof Sign){
            $key = ["Free", "Gamemode", "Heal", "Kit", "Repair", "Time", "Teleport", "Warp"];
            foreach($key as $k){
                if($tile->getText()[0] === "[" . $k . "]" && !$event->getPlayer()->hasPermission("essentials.sign.break." . strtolower($k))){
                    $event->setCancelled(true);
                    $event->getPlayer()->sendMessage(TextFormat::RED . "You don't have permissions to break this sign");
                    break;
                }
            }
        }
    }

    /**
     * @param SignChangeEvent $event
     */
    public function onSignChange(SignChangeEvent $event){
        // Colored Sign
        if($event->getPlayer()->hasPermission("essentials.sign.color")){
            $event->setLine(0, $this->plugin->colorMessage($event->getLine(0)));
            $event->setLine(1, $this->plugin->colorMessage($event->getLine(1)));
            $event->setLine(2, $this->plugin->colorMessage($event->getLine(2)));
            $event->setLine(3, $this->plugin->colorMessage($event->getLine(3)));
        }

        // Special Signs
        // Free sign
        if(strtolower($event->getLine(0)) === "[free]" && $event->getPlayer()->hasPermission("essentials.sign.create.free")){
            if(trim($event->getLine(1)) !== "" || $event->getLine(1) !== null){
                $item_name = $event->getLine(1);

                if(trim($event->getLine(2)) !== "" || $event->getLine(2) !== null){
                    $damage = $event->getLine(2);
                }else{
                    $damage = 0;
                }

                $item = $this->plugin->getItem($item_name . ":" . $damage);

                if($item->getID() === 0 || $item->getName() === "Air"){
                    $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Invalid item name/ID");
                    $event->setCancelled(true);
                }else{
                    $event->getPlayer()->sendMessage(TextFormat::GREEN . "Free sign successfully created!");
                    $event->setLine(0, "[Free]");
                    $event->setLine(1, ($item->getName() === "Unknown" ? $item->getID() : $item->getName()));
                    $event->setLine(2, $damage);
                }
            }else{
                $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] You should provide an item name/ID");
                $event->setCancelled(true);
            }
        }

        // Gamemode sign
        elseif(strtolower($event->getLine(0)) === "[gamemode]" && $event->getPlayer()->hasPermission("essentials.sign.create.gamemode")){
            switch(strtolower($event->getLine(1))){
                case "survival":
                case "0":
                    $event->setLine(1, "Survival");
                    break;
                case "creative":
                case "1":
                    $event->setLine(1, "Creative");
                    break;
                case "adventure":
                case "2":
                    $event->setLine(1, "Adventure");
                    break;
                case "spectator":
                case "view":
                case "3":
                    $event->setLine(1, "Spectator");
                    break;
                default:
                    $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Unknown Gamemode, you should use \"Survival\", \"Creative\", \"Adventure\" or \"Spectator\"");
                    $event->setCancelled(true);
                    return;
                    break;
            }
            $event->getPlayer()->sendMessage(TextFormat::GREEN . "Gamemode sign successfully created!");
            $event->setLine(0, "[Gamemode]");
        }

        // Heal sign
        elseif(strtolower($event->getLine(0)) === "[heal]" && $event->getPlayer()->hasPermission("essentials.sign.create.heal")){
            $event->getPlayer()->sendMessage(TextFormat::GREEN . "Heal sign successfully created!");
            $event->setLine(0, "[Heal]");
        }

        // Kit sign
        elseif(strtolower($event->getLine(0)) === "[kit]" && $event->getPlayer()->hasPermission("essentials.sign.create.kit")){
            if(!$this->plugin->kitExists($event->getLine(1))){
                $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Kit doesn't exist");
                return;
            }
            $event->getPlayer()->sendMessage(TextFormat::GREEN . "Kit sign successfully created!");
            $event->setLine(0, "[Kit]");
        }

        // Repair sign
        elseif(strtolower($event->getLine(0)) === "[repair]" && $event->getPlayer()->hasPermission("essentials.sign.create.repair")){
            switch(strtolower($event->getLine(1))){
                case "hand":
                    $event->setLine(1, "Hand");
                    break;
                case "all":
                    $event->setLine(1, "All");
                    break;
                default:
                    $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Invalid argument, you should use \"Hand\" or \"All\"");
                    $event->setCancelled(true);
                    return;
                    break;
            }
            $event->getPlayer()->sendMessage(TextFormat::GREEN . "Repair sign successfully created!");
            $event->setLine(0, "[Repair]");
        }

        // Time sign
        elseif(strtolower($event->getLine(0)) === "[time]" && $event->getPlayer()->hasPermission("essentials.sign.create.time")){
            switch(strtolower($event->getLine(1))){
                case "day":
                    $event->setLine(1, "Day");
                    break;
                case "night";
                    $event->setLine(1, "Night");
                    break;
                default:
                    $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Invalid time, you should use \"Day\" or \"Night\"");
                    $event->setCancelled(true);
                    return;
                    break;
            }
            $event->getPlayer()->sendMessage(TextFormat::GREEN . "Time sign successfully created!");
            $event->setLine(0, "[Time]");
        }

        // Teleport sign
        elseif(strtolower($event->getLine(0)) === "[teleport]" && $event->getPlayer()->hasPermission("essentials.sign.create.teleport")){
            if(!is_numeric($event->getLine(1))){
                $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Invalid X position, Teleport sign will not work");
                $event->setCancelled(true);
            }elseif(!is_numeric($event->getLine(2))){
                $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Invalid Y position, Teleport sign will not work");
                $event->setCancelled(true);
            }elseif(!is_numeric($event->getLine(3))){
                $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Invalid Z position, Teleport sign will not work");
                $event->setCancelled(true);
            }else{
                $event->getPlayer()->sendMessage(TextFormat::GREEN . "Teleport sign successfully created!");
                $event->setLine(0, "[Teleport]");
                $event->setLine(1, $event->getLine(1));
                $event->setLine(2, $event->getLine(2));
                $event->setLine(3, $event->getLine(3));
            }
        }

        // Warp sign
        elseif(strtolower($event->getLine(0)) === "[warp]" && $event->getPlayer()->hasPermission("essentials.sign.create.warp")){
            $warp = $event->getLine(1);
            if(!$this->plugin->warpExists($warp)){
                $event->getPlayer()->sendMessage(TextFormat::RED . "[Error] Warp doesn't exists");
                $event->setCancelled(true);
            }else{
                $event->getPlayer()->sendMessage(TextFormat::GREEN . "Warp sign successfully created!");
                $event->setLine(0, "[Warp]");
            }
        }
    }
}