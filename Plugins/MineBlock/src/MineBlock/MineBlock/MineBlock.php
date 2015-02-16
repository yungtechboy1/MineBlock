<?php
namespace MineBlock\MineBlock;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use pocketmine\block\Block;

class MineBlock extends PluginBase implements Listener{

	public function onEnable(){
		$this->item = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$set = $this->set->getAll();
		$drop = $this->drop->getAll();
		$rm = TextFormat::RED . "Usage: /MineBlock ";
		$mm = "[MineBlock] MineBlock ";
		switch(strtolower($sub[0])){
			case "mine":
			case "m":
			case "on":
			case "off":
			case "마인":
			case "마인블럭":
			case "광물블럭":
			case "온":
			case "오프":
				if($set["Mine"] == "On"){
					$set["Mine"] = "Off";
					$r = ($this->isKorean() ? "마인블럭을  끕니다.": "MineBlock is Off");
				}else{
					$set["Mine"] = "On";
					$r = ($this->isKorean() ? "마인블럭을 켭니다.": "MineBlock is On");
				}
			break;
			case "regen":
			case "r":
			case "리젠":
			case "소생":
				if($set["Regen"] == "On"){
					$set["Regen"] = "Off";
					$r = ($this->isKorean() ? "블럭리젠을  끕니다.": "Regen is Off");
				}else{
					$set["Regen"] = "On";
					$r = ($this->isKorean() ? "블럭리젠을 켭니다.": "Regen is On");
				}
			break;
			case "block":
			case "b":
			case "블럭":
			case "광물":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "블럭 <블럭ID>": $rm . "Block(B) <BlockID>");
				}else{
					$i = Item::fromString($sub[1]);
					$i = $i->getID() . ":" . $i->getDamage();
					$set["Block"] = $i;
					$r = ($this->isKorean() ? "블럭을 [$i] 로 설정했습니다.": "Block is set [$i]");
				}
			break;
			case "delay":
			case "d":
			case "time":
			case "t":
			case "딜레이":
			case "시간":
			case "타임":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "딜레이 <시간>": $rm . "Delay(D) <Num>");
				}else{
					if($sub[1] < 0 || !is_numeric($sub[1])) $sub[1] = 0;
					if(isset($sub[2]) && $sub[2] > $sub[1] && is_numeric($sub[2]) !== false) $sub[1] = $sub[1] . "~" . $sub[2];
					$set["Time"] = $sub[1];
					$r = ($this->isKorean() ? "블럭리젠 딜레이를 [$sub[1]] 로 설정했습니다.": "Block Regen Delay is set [$sub[1]]");
				}
			break;
			case "count":
			case "c":
			case "갯수":
			case "횟수":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "횟수 <횟수>": $rm . "Count(C) <Num>");
				}else{
					if($sub[1] < 1 || !is_numeric($sub[1])) $sub[1] = 1;
					if(isset($sub[2]) && $sub[2] > $sub[1] && is_numeric($sub[2]) !== false) $sub[1] = $sub[1] . "~" . $sub[2];
					$set["Count"] = $sub[1];
					$r = ($this->isKorean() ? "드랍 횟수를 [$sub[1]] 로 설정했습니다.": "Drop count is set [$sub[1]]");
				}
			break;
			case "drop":
			case "drops":
			case "dr":
			case "드롭":
			case "드롭템":
			case "드랍":
			case "드랍템":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "드롭 <추가|삭제|리셋|목록>": $rm . "Drops(Dr) <Add|Del|Reset|List>");
				}else{
					switch(strtolower($sub[1])){
						case "add":
						case "a":
						case "추가":
							if(!isset($sub[2]) || !isset($sub[3])){
								$r = ($this->isKorean() ? $rm . "드롭템 추가 <아이템ID> <확률> <갯수1> <갯수2>": $rm . "Fishs(F) Add(A) <ItemID> <Petsent> <Count1> <Count2>");
							}else{
								$i = Item::fromString($sub[2]);
								if($sub[3] < 1 || !is_numeric($sub[3])) $sub[3] = 1;
								if(!isset($sub[4]) < 0 || !is_numeric($sub[4])) $sub[4] = 0;
								if(isset($sub[5]) && $sub[5] > $sub[4] && is_numeric($sub[5])) $sub[4] = $sub[4] . "~" . $sub[5];
								$drop[] = ["Percent" => $sub[3],"ID" => $i->getID() . ":" . $i->getDamage(),"Count" => $sub[4]];
								$r = ($this->isKorean() ? "드롭템 추가됨 [" . $i->getID() . ":" . $i->getDamage() . " 갯수:$sub[4] 확률:$sub[3]]": "Drops add [" . $i->getID() . ":" . $i->getDamage() . " Count:$sub[4] Persent:$sub[3]]");
							}
						break;
						case "del":
						case "d":
						case "삭제":
						case "제거":
							if(!isset($sub[2])){
								$r = ($this->isKorean() ? $rm . "드롭템 삭제 <번호>": $rm . "Fishs(F) Del(D) <FishNum>");
							}else{
								if($sub[2] < 0 || !is_numeric($sub[2])) $sub[2] = 0;
								if(!isset($drop[$sub[2] - 1])){
									$r = ($this->isKorean() ? "[$sub[2]] 는 존재하지않습니다. \n  " . $rm . "드롭템 목록 ": "[$sub[2]] does not exist.\n  " . $rm . "Drops(Dr) List(L)");
								}else{
									$d = $drop[$sub[2] - 1];
									unset($drop[$sub[2] - 1]);
									$r = ($this->isKorean() ? "드롭템 제거됨 [" . $i->getID() . ":" . $i->getDamage() . " 갯수:$sub[4] 확률:$sub[3]]": "Drop del [" . $d["ID"] . " Count:" . $d["Count"] . " Persent:" . $d["Percent"] . "]");
								}
							}
						break;
						case "reset":
						case "r":
						case "리셋":
						case "초기화":
							$drop = [];
							$r = ($this->isKorean() ? "드롭템 목록을 초기화합니다.": "Drop list is Reset");
						break;
						case "list":
						case "l":
						case "목록":
						case "리스트":
							$page = 1;
							if(isset($sub[2]) && is_numeric($sub[2])) $page = round($sub[2]);
							$list = ceil(count($drop) / 5);
							if($page >= $list) $page = $list;
							$r = ($this->isKorean() ? "목록 (페이지 $page/$list) \n": "List (Page $page/$list) \n");
							$num = 0;
							foreach($drop as $k){
								$num++;
								if($num + 5 > $page * 5 && $num <= $page * 5) $r .= ($this->isKorean() ? "  [$num] 아이디:" . $k["ID"] . " 갯수:" . $k["Count"] . " 확률:" . $k["Percent"] . " \n": "  [$num] ID:" . $k["ID"] . " Count:" . $k["Count"] . " Percent:" . $k["Percent"] . " \n");
							}
						break;
						default:
							return false;
						break;
					}
				}
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		$this->set->setAll($set);
		$this->drop->setAll($drop);
		$this->saveYml();
		return true;
	}

	public function onBlockBreak(BlockBreakEvent $event){
		if($event->isCancelled()) return;
		$b = $event->getBlock();
		if($this->set->get("Mine") == "Off") return;
		$bb = Item::fromString($this->set->get("Block"));
		if($bb->getID() !== $b->getID() || $bb->getDamage() !== $b->getDamage()) return;
		foreach($b->getDrops($event->getItem()) as $i)
			$this->item[$i[0] . ":" . $i[1] . ":" . $i[2]] = true;
		if($this->set->get("Regen") == "On") $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"mineRegen"], [clone $b]), $this->getTime());
		for($for = 0; $for < $this->getCount(); $for++)
			$b->getLevel()->dropItem($b, $this->getDrop());
		$event->setCancelled();
	}

	public function onItemSpawn(ItemSpawnEvent $event){
		$entity = $event->getEntity();
		$i = $entity->getItem();
		$item = $i->getID() . ":" . $i->getDamage() . ":" . $i->getCount();
		if(isset($this->itrm[$item])){
			unset($this->item[$item]);
			$entity->close();
		}
	}

	public function mineRegen($b){
		$b->getLevel()->setBlock($b, $b, false);
	}

	public function getCount(){
		$c = explode("~", $this->set->get("Count"));
		if(isset($c[1])){
			$cnt = rand($c[0], $c[1]);
		}else{
			$cnt = $c[0];
		}
		return $cnt;
	}

	public function getTime(){
		$t = explode("~", $this->set->get("Time"));
		if(isset($t[1])){
			$tt = rand($t[0], $t[1]);
		}else{
			$tt = $t[0];
		}
		return $tt * 20;
	}

	public function getDrop(){
		$d = $this->drops;
		shuffle($d);
		$d = array_shift($d);
		$i = Item::fromString($d["ID"]);
		$c = explode("~", $d["Count"]);
		$i->setCount($c[0]);
		if(isset($c[1])) $i->setCount(rand($c[0], $c[1]));
		return $i;
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! MineBlock/MineBlock/");
		$this->set = new Config($this->getServer()->getDataPath() . "/plugins/! MineBlock/MineBlock/" . "Setting.yml", Config::YAML, ["Block" => "48:0","Mine" => "On","Regen" => "On","Time" => "3~5","Count" => "1~2"]);
		if(is_file($this->getServer()->getDataPath() . "/plugins/! MineBlock/MineBlock/" . "Drops.yml") == true){
			$drop = [];
		}else{
			$drop = [["Percent" => 700,"ID" => "4:0","Count" => "1"],["Percent" => 70,"ID" => "263","Count" => "1~3"],["Percent" => 50,"ID" => "15:0","Count" => "1"],["Percent" => 20,"ID" => "331:0","Count" => "1~7"],["Percent" => 15,"ID" => "14:0","Count" => "1"],["Percent" => 5,"ID" => "351:4","Count" => "1~7"],["Percent" => 3,"ID" => "388:0","Count" => "1"],["Percent" => 1,"ID" => "264:0","Count" => "1"]];
		}
		$this->drop = new Config($this->getServer()->getDataPath() . "/plugins/! MineBlock/MineBlock/" . "Drops.yml", Config::YAML, $drop);
		$this->drops = [];
		foreach($this->drop->getAll() as $drop){
			for($for = 0; $for < $drop["Percent"]; $for++)
				$this->drops[] = $drop;
		}
	}

	public function saveYml(){
		$this->set->save();
		$this->drop->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! MineBlock/" . "! Korean.yml", Config::YAML, ["Korean" => false]))->get("Korean");
	}
}