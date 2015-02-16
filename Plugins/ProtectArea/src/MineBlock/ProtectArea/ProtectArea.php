<?php
namespace MineBlock\ProtectArea;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;

class ProtectArea extends PluginBase implements Listener{

	public function onEnable(){
		$this->touch = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$pa = $this->protect->getAll();
		$rm = "Usage: /ProtectArea ";
		$mm = "[ProteckArea] ";
		$n = $sender->getName();
		$t = $this->touch;
		switch(strtolower($sub[0])){
			case "add":
			case "a":
				if(isset($t[$n])){
					$r = "Touch Disable";
					unset($t[$n]);
				}else{
					$r = "Touch the Pos1";
					$t[$n] = ["Type" => "Add","Area" => false];
				}
			break;
			case "del":
			case "d":
				if(isset($t[$n])){
					$r = "Touch Disable";
					unset($t[$n]);
				}else{
					$r = "Touch the Area";
					$t[$n] = ["Type" => "Del"];
				}
			break;
			case "reset":
			case "r":
				$pa = [];
				$r = " Reset";
			break;
			case "list":
			case "l":
				$page = 1;
				if(isset($sub[0]) && is_numeric($sub[0])) $page = round($sub[0]);
				$list = ceil(count($pa) / 5);
				if($page >= $list) $page = $list;
				$r = "List (Page $page/$list) \n";
				$num = 0;
				foreach($pa as $v){
					$num++;
					if($num + 5 > $page * 5 && $num <= $page * 5) $r .= "  [$num] X: " . $v["X"] . " Y: " . $v["Y"] . " Z: " . $v["Z"] . " W: " . $v["W"] . " \n";
				}
			break;
			case "load":
			case "rl":
				$this->loadYml();
				$r = " Reload";
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->pa = $pa;
		$this->touch = $t;
		$this->saveYml();
		return true;
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		if(!$event->isCancelled()) $this->checkArea($event);
	}

	public function onBlockBreak(BlockBreakEvent $event){
		if(!$event->isCancelled()) $this->checkArea($event);
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		if(!$event->isCancelled()) $this->checkArea($event);
	}

	public function protectArea($event, $type = false){
		$p = $event->getPlayer();
		if($type || !$p->isOp()){
			$b = $event->getBlock();
			$this->asortArea();
			foreach($this->pa as $k => $v){
				$bx = $b->x;
				$by = $b->y;
				$bz = $b->z;
				$x = explode("~", $v["X"]);
				$y = explode("~", $v["Y"]);
				$z = explode("~", $v["Z"]);
				if($b->getLevel()->getName() == $v["W"] && $bx >= $x[0] && $bx <= $x[1] && $by >= $y[0] && $by <= $y[1] && $bz >= $z[0] && $bz <= $z[1]){
					$event->setCancelled();
					if($type){
						$p->sendMessage("/��ProtectArea] Delete ProtectArea \n/��X: " . $v["X"] . " Y: " . $v["Y"] . " Z: " . $v["Z"] . " W: " . $v["W"]);
						unset($this->pa[$k]);
						$this->saveYml();
					}elseif(!$type){
						$p->sendMessage("/��ProtectArea] Thare is Protected Area");
					}
					return true;
				}
			}
		}
		return false;
	}

	public function checkArea($event){
		$p = $event->getPlayer();
		$n = $p->getName();
		$t = $this->touch;
		if(isset($t[$n])){
			$pa = $this->pa;
			$r = "[ProteckArea] ";
			$b = $event->getBlock();
			switch($t[$n]["Type"]){
				case "Add":
					$area = $t[$n]["Area"];
					if($this->protectArea($event, -1)){
						$r .= "Thare is already ProtectArea";
					}elseif(!$area){
						$t[$n]["Area"] = [$b->x,$b->y,$b->z,$b->getLevel()->getName()];
						$v = $t[$n]["Area"];
						$r .= "Pos1 : X: " . $v[0] . " Y: " . $v[1] . " Z: " . $v[2] . " W: " . $v[3] . " \n";
						$r .= "\n/�� Touch the Pos2";
					}else{
						$v = ["X" => $area[0] . "~" . $b->z,"Y" => $area[1] . "~" . $b->y,"Z" => $area[2] . "~" . $b->z,"W" => $area[3]];
						$pa[] = $v;
						$r .= "Pos2 : X: " . $v["X"] . " Y: " . $v["Y"] . " Z: " . $v["Z"] . " W: " . $v["W"] . " \n";
						$r .= "\n Make ProtectArea";
						unset($t[$n]);
					}
				break;
				case "Del":
					if(!$this->protectArea($event, true)) $r .= "Thare is not ProtectArea";
				break;
				default:
					return false;
			}
			$this->touch = $t;
			$p->sendMessage($r);
			$this->pa = $pa;
			$this->saveYml();
		}else{
			$this->protectArea($event);
		}
	}

	public function asortArea(){
		$pa = [];
		foreach($this->pa as $k => $v){
			$x = explode("~", $v["X"]);
			asort($x);
			$x = implode("~", $x);
			$y = explode("~", $v["Y"]);
			asort($y);
			$y = implode("~", $y);
			$z = explode("~", $v["Z"]);
			asort($z);
			$z = implode("~", $z);
			$pa[] = ["X" => $x,"Y" => $y,"Z" => $z,"W" => $v["W"]];
		}
		$this->pa = $pa;
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! MineBlock/");
		$this->protect = new Config($this->getServer()->getDataPath() . "/plugins/! MineBlock/" . "ProtectArea.yml", Config::YAML, []);
		$this->pa = $this->protect->getAll();
	}

	public function saveYml(){
		$this->asortArea();
		$this->protect->setAll($this->pa);
		$this->protect->save();
		$this->loadYml();
	}
}