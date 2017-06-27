<?php


/**
 *
 * 8888888b.                   888               888    8888888888                                    888    d8b          888
 * 888   Y88b                  888               888    888                                           888    Y8P          888
 * 888    888                  888               888    888                                           888                 888
 * 888   d88P .d88b.   .d8888b 888  888  .d88b.  888888 8888888   .d8888b  .d8888b   .d88b.  88888b.  888888 888  8888b.  888
 * 8888888P" d88""88b d88P"    888 .88P d8P  Y8b 888    888       88K      88K      d8P  Y8b 888 "88b 888    888     "88b 888
 * 888       888  888 888      888888K  88888888 888    888       "Y8888b. "Y8888b. 88888888 888  888 888    888 .d888888 888
 * 888       Y88..88P Y88b.    888 "88b Y8b.     Y88b.  888            X88      X88 Y8b.     888  888 Y88b.  888 888  888 888
 * 888        "Y88P"   "Y8888P 888  888  "Y8888   "Y888 8888888888 88888P'  88888P'  "Y8888  888  888  "Y888 888 "Y888888 888
 *
 * Copyright (C) 2016 PocketEssential
 *
 * This is a public software, you cannot redistribute it a and/or modify any way
 * unless otherwise given permission to do so.
 *
 * @author PocketEssential
 * @link https://github.com/PocketEssential/
 *
 */

namespace PocketEssential\DailyRewards;

use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements  Listener
{
    public $cooldown = 86400;
    public $claimed;
    public $already_claimed;

    public function onEnable()
    {
		$this->saveDefaultConfig();
		if(!is_dir($this->getDataFolder()."players")) mkdir($this->getDataFolder() . "players");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->claimed = $this->getConfig()->get("Claimed");
        $this->already_claimed = $this->getConfig()->get("Already_Claimed");
		$this->getLogger()->info(TextFormat::DARK_BLUE . "DailyRewards by PocketEssential " . TextFormat::DARK_PURPLE . "has been enabled!");
		
    }

    public function onDisable()
    {
        $this->getLogger()->info(TextFormat::DARK_BLUE . "DailyRewards has turned off, Did the server stop?");
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
		if($sender instanceof Player){
			$cmd = strtolower($command->getName());
			if ($cmd === "dailyrewards") {
				$this->giveReward($sender);
				return true;
			}
		} else {
			$sender->sendMessage(TextFormat::RED . "Run this command in game!");
		}
    }
	
	public function onJoin(PlayerJoinEvent $ev){
		$name = $ev->getPlayer()->getName();
		if($this->isFirstJoin($name)){
			$this->registerConfig($name);
		}
	}
	
	public function getPlayerConfig($player){
		return (new Config($this->getDataFolder() . "players/". strtolower($player) .".json", Config::JSON));
	}
	
	public function registerConfig($player){
		$config = new Config($this->getDataFolder() . "players/".$player.".json", Config::JSON, [
		"time" => time()
		]);
		$config->save();
	}
	
	public function isFirstJoin($player){
		return !file_exists($this->getDataFolder() . "players/".$player.".json");
	}
	
    public function giveReward($sender){
		if($sender instanceof Player){
			$cfg = $this->getPlayerConfig($sender->getName());
			if((time() - $cfg->get("time")) >= 86400){
				$sender->sendMessage($this->claimed);
				$name = $sender->getName();
				$RewardCommand = $this->getConfig()->get("RewardCommand");
				$Rewards = str_replace( "{player}", "$name", $RewardCommand);
				$time = (time() + $this->cooldown);
				$cfg->set("time", $time);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $Rewards);
			} else {
				$sender->sendMessage($this->already_claimed);
			}
		}
    }
}
