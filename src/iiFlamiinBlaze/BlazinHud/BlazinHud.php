<?php
/**
 *  ____  _            _______ _          _____
 * |  _ \| |          |__   __| |        |  __ \
 * | |_) | | __ _ _______| |  | |__   ___| |  | | _____   __
 * |  _ <| |/ _` |_  / _ \ |  | '_ \ / _ \ |  | |/ _ \ \ / /
 * | |_) | | (_| |/ /  __/ |  | | | |  __/ |__| |  __/\ V /
 * |____/|_|\__,_/___\___|_|  |_| |_|\___|_____/ \___| \_/
 *
 * Copyright (C) 2018 iiFlamiinBlaze
 *
 * iiFlamiinBlaze's plugins are licensed under MIT license!
 * Made by iiFlamiinBlaze for the PocketMine-MP Community!
 *
 * @author iiFlamiinBlaze
 * Twitter: https://twitter.com/iiFlamiinBlaze
 * GitHub: https://github.com/iiFlamiinBlaze
 * Discord: https://discord.gg/znEsFsG
 */
declare(strict_types=1);

namespace iiFlamiinBlaze\BlazinHud;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class BlazinHud extends PluginBase implements Listener{

    const VERSION = "v1.0.1";
    const PREFIX = TextFormat::AQUA . "BlazinHud" . TextFormat::GOLD . " > ";

    /** @var self $instance */
    private static $instance;

    public function onEnable() : void{
        self::$instance = $this;
        $this->getLogger()->info("BlazinHud " . self::VERSION . " by iiFlamiinBlaze is enabled");
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onJoin(PlayerJoinEvent $event) : void{
        $this->multiWorldCheck($event->getPlayer());
    }

    public function onLevelChange(EntityLevelChangeEvent $event) : void{
        $player = $event->getEntity();
        if(!$player instanceof Player) return;
        $this->multiWorldCheck($player);
    }

    private function multiWorldCheck(Player $player) : bool{
        if($this->getConfig()->get("multi-world") === "on"){
            if(in_array($player->getLevel()->getName(), $this->getConfig()->get("worlds"))){
                $this->getServer()->getScheduler()->scheduleRepeatingTask(new HudTask($this), 30);
            }else{
                $player->sendMessage(self::PREFIX . TextFormat::RED . "You are not in the right world for your hud to appear");
                return false;
            }
        }elseif($this->getConfig()->get("multi-world") === "off"){
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new HudTask($this), 30);
            return false;
        }
        return true;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if($command->getName() === "blazinhud"){
            if(!$sender instanceof Player){
                $sender->sendMessage(self::PREFIX . TextFormat::RED . "Use this command in-game");
                return false;
            }
            if(!$sender->hasPermission("blazinhud.command")){
                $sender->sendMessage(self::PREFIX . TextFormat::RED . "You do not have permission to use this command");
                return false;
            }
            if(empty($args)){
                $sender->sendMessage(self::PREFIX . TextFormat::GRAY . "Usage: /blazinhud <info | set | reload> <hud> <message>");
                return false;
            }
            switch($args[0]){
                case "info":
                    $sender->sendMessage(TextFormat::DARK_GRAY . "-=========" . TextFormat::GOLD . "BlazinHud " . self::VERSION . TextFormat::DARK_GRAY . "=========-");
                    $sender->sendMessage(TextFormat::GREEN . "Author: " . TextFormat::AQUA . "BlazeTheDev");
                    $sender->sendMessage(TextFormat::GREEN . "GitHub: " . TextFormat::AQUA . "https://github.com/iiFlamiinBlaze");
                    $sender->sendMessage(TextFormat::GREEN . "Support: " . TextFormat::AQUA . "https://discord.gg/znEsFsG");
                    $sender->sendMessage(TextFormat::GREEN . "Description: " . TextFormat::AQUA . "Allows you to customize a message that will pop up above your hotbar");
                    $sender->sendMessage(TextFormat::DARK_GRAY . "-===============================-");
                    break;
                case "set":
                    if($args[1]){
                        switch($args[1]){
                            case "hud":
                                if($args[2]){
                                    if(is_string($args[2])){
                                        $this->getConfig()->set("hud-message", $args[2]);
                                        $this->getConfig()->save();
                                        $sender->sendMessage(self::PREFIX . TextFormat::GREEN . "You have successfully set the hud to $args[2]");
                                    }else{
                                        $sender->sendMessage(self::PREFIX . TextFormat::RED . "Please enter a string to set the hud too");
                                        return false;
                                    }
                                }else{
                                    $sender->sendMessage(self::PREFIX . TextFormat::GRAY . "Usage: /blazinhud <info | set | reload> <hud> <message>");
                                    return false;
                                }
                                break;
                            default:
                                $sender->sendMessage(self::PREFIX . TextFormat::GRAY . "Usage: /blazinhud <info | set | reload> <hud> <message>");
                                break;
                        }
                    }else{
                        $sender->sendMessage(self::PREFIX . TextFormat::GRAY . "Usage: /blazinhud <info | set | reload> <hud> <message>");
                        return false;
                    }
                    break;
                case "reload":
                    $this->getConfig()->save();
                    $this->getConfig()->reload();
                    $sender->sendMessage(self::PREFIX . TextFormat::GREEN . "BlazinHud successfully reloaded");
                    break;
                default:
                    $sender->sendMessage(self::PREFIX . TextFormat::GRAY . "Usage: /blazinhud <info | set | reload> <hud> <message>");
                    break;
            }
        }
        return true;
    }

    public static function getInstance() : self{
        return self::$instance;
    }
}