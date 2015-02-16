<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\MineBlock;

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
		$this->block = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
/*		$set = $this->set->getAll();
		$drop = $this->mb->getAll();
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
					$r = ($this->isKorean() ? "마인블럭을  끕니다."? "MineBlock is Off");
				}else{
					$set["Mine"] = "On";
					$r = ($this->isKorean() ? "마인블럭을 켭니다."? "MineBlock is On");
				}
			break;
			case "regen":
			case "r":
			case "리젠":
			case "소생":
				if($set["Regen"] == "On"){
					$set["Regen"] = "Off";
					$r = ($this->isKorean() ? "블럭리젠을  끕니다."? "Regen is Off";
				}else{
					$set["Regen"] = "On";
					$r = ($this->isKorean() ? "블럭리젠을 켭니다."? "Regen is On";
				}
			break;
			case "block":
			case "b":
			case "블럭":
			case "광물":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "블럭 <블럭ID>": $rm . "Block(B) <BlockID>";
				}else{
					$i = Item::fromString($sub[1]);
					$i = $i->getID() . ":" . $i->getDamage();
					$set["Block"] = $i;
					$r = ($this->isKorean() ? "블럭을 [$i] 로 설정했습니다."? "Block is set [$i]";
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
					$r = ($this->isKorean() ? $rm . "딜레이 <시간>": $rm . "Delay(D) <Num>";
				}else{
					if($sub[1] < 0 || !is_numeric($sub[1])) $sub[1] = 0;
					if(isset($sub[2]) && $sub[2] > $sub[1] && is_numeric($sub[2]) !== false) $sub[1] = $sub[1] . "~" . $sub[2];
					$set["Time"] = $sub[1];
					$r = ($this->isKorean() ? "블럭리젠 딜레이를 [$sub[1]] 로 설정했습니다."? "Block Regen Delay is set [$sub[1]]";
				}
			break;
			case "count":
			case "c":
			case "갯수":
			case "횟수":
				if(!isset($sub[1])){
					$r = ($this->isKorean() ? $rm . "횟수 <횟수>": $rm . "Count(C) <Num>";
				}else{
					if($sub[1] < 1 || !is_numeric($sub[1])) $sub[1] = 1;
					if(isset($sub[2]) && $sub[2] > $sub[1] && is_numeric($sub[2]) !== false) $sub[1] = $sub[1] . "~" . $sub[2];
					$set["Count"] = $sub[1];
					$r = ($this->isKorean() ? "드랍 횟수를 [$sub[1]] 로 설정했습니다."? "Drop count is set [$sub[1]]";
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
					$r = ($this->isKorean() ? $rm . "드롭 <추가|삭제|리셋|목록>": $rm . "Drops(Dr) <Add|Del|Reset|List>";
				}else{
					switch(strtolower($sub[1])){
						case "add":
						case "a":
						case "추가":
							if(!isset($sub[2]) || !isset($sub[3])){
								$r = ($this->isKorean() ? $rm . "드롭템 추가 <아이템ID> <확률> <갯수1> <갯수2>": $rm . "Fishs(F) Add(A) <ItemID> <Petsent> <Count1> <Count2>";
							}else{
								$i = Item::fromString($sub[2]);
								if($sub[3] < 1 || !is_numeric($sub[3])) $sub[3] = 1;
								if(!isset($sub[4]) < 0 || !is_numeric($sub[4])) $sub[4] = 0;
								if(isset($sub[5]) && $sub[5] > $sub[4] && is_numeric($sub[5])) $sub[4] = $sub[4] . "~" . $sub[5];
								$drop[] = [$sub[3], $i->getID() . ":" . $i->getDamage(), $sub[4] ];
								$r = ($this->isKorean() ? "드롭템 추가됨 [" . $i->getID() . ":" . $i->getDamage() . " 갯수:$sub[4] 확률:$sub[3]]"? "Drops add [" . $i->getID() . ":" . $i->getDamage() . " Count:$sub[4] Persent:$sub[3]]";
							}
						break;
						case "del":
						case "d":
						case "삭제":
						case "제거":
							if(!isset($sub[2])){
								$r = ($this->isKorean() ? $rm . "드롭템 삭제 <번호>": $rm . "Fishs(F) Del(D) <FishNum>";
							}else{
								if($sub[2] < 0 || !is_numeric($sub[2])) $sub[2] = 0;
								if(!isset($drop[$sub[2] - 1])){
									$r = ($this->isKorean() ? "[$sub[2]] 는 존재하지않습니다. \n  " . $rm . "드롭템 목록 "? "[$sub[2]] does not exist.\n  " . $rm . "Drops(Dr) List(L)";
								}else{
									$d = $drop[$sub[2] - 1];
									unset($drop[$sub[2] - 1]);
									$r ? "Drop del [" . $d["ID"] . " Count:" . $d["Count"] . " Persent:" . $d["Percent"] . "]";
								}
							}
						break;
						case "reset":
						case "r":
						case "리셋":
						case "초기화":
							$drop = [];
							$r = ($this->isKorean() ? "드롭템 목록을 초기화합니다."? "Drop list is Reset";
						break;
						case "list":
						case "l":
						case "목록":
						case "리스트":
							$page = 1;
							if(isset($sub[2]) && is_numeric($sub[2])) $page = round($sub[2]);
							$list = ceil(count($drop) / 5);
							if($page >= $list) $page = $list;
							$r = ($this->isKorean() ? "목록 (페이지 $page/$list) \n"? "List (Page $page/$list) \n";
							$num = 0;
							foreach($drop as $k){
								$num++;
								if($num + 5 > $page * 5 && $num <= $page * 5) $r .= ($this->isKorean() ? "  [$num] 아이디:" . $k["ID"] . " 갯수:" . $k["Count"] . " 확률:" . $k["Percent"] . " \n": "  [$num] ID:" . $k["ID"] . " Count:" . $k["Count"] . " Percent:" . $k["Percent"] . " \n";
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
		$this->mb->setAll($drop);
		$this->saveYml();
*/		return true;
	}

	public function onBlockBreak(BlockBreakEvent $event){
		if($event->isCancelled()) return;
		$b = $event->getBlock();
		$key = $this->getKey($b);
		if(!isset($this->mb[$key])) return;
		if(!$event->getPlayer()->hasPermission("debe.mineblock.break")) return $event->setCancelled();
		$mb = $this->mb[$key];
		$rand = $this->rand[$key];
		foreach($b->getDrops($event->getItem()) as $i)
			$this->item[$i[0] . ":" . $i[1] . ":" . $i[2]] = true;
		if($mb[0] == true) $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"mineRegen" ], [clone $b]), $this->randStr($mb[1]));
		for($for = 0; $for < $this->randStr($mb[2]); $for++)
			$b->getLevel()->dropItem($b, $this->getDrop($mb,$rand));
		$event->setCancelled();
	}

	public function onItemSpawn(ItemSpawnEvent $event){
		$entity = $event->getEntity();
		$i = $entity->getItem();
		$item = $i->getID() . ":" . $i->getDamage() . ":" . $i->getCount();
		if(isset($this->item[$item])){
			unset($this->item[$item]);
			$entity->close();
		}
	}

	public function getDrop($mb,$rand){
		$mb = $this->mb[$key];
		$rand = $this->rand[$key];
		$r = rand(1,$rand[1]);
		ksort($rand[0]);
		foreach($rand[0] as $d => $p){
			if($rand < $p){
				$i = Item::fromString($d);
				$i->setCount($this->randStr($mb[d][1]));
				return $i;
			}
		}
	}

	public function mineRegen($b,$id){
		$pos = $this->getPos($b);
		if(isset($this->block[$pos])){
			$b->getLevel()->setBlock($b,$b);
			unset($this->block[$pos]);
		}
	}

	public function getKey($i){
		return $i->getID().":".$i->getDamage();
	}
	
	public function getPos($b){
		return $b->getX().":".$b->getY().":".$b->getZ().":".$b->getLevel()->getName();
	}

	public function randStr($str){
		$a = explode("~", $str);		
		return isset($a[1]) ? rand($a[0], $a[1]) : $a[0];
	}

	public function loadYml(){
		$path = $this->getServer()->getDataPath() . "/plugins/! DeBePlugins/";
		@mkdir($path);
		$pathC = $path . "MineBlock.yml";
		$this->mineBlock = new Config($pathC, Config::YAML, is_file($pathC) == true ? [] : ["48:0" => [true, "3~5", "1~1", ["4:0" => [700,1],"263:0" => [70,"1~3"],"15:0" => [50,1],"331:0" => [20,"1~7"],"14:0" => [15,1],"351:4" => [5,"1~7"],"388:0" => [3,1],"264:0" => [1,1]]]]);
		$mb = $this->mineBlock->getAll();
		$this->mb = $mb;
		$max = [];
		$rand = [];
		foreach($mb as $k => $v){
			foreach($v[3] as $p => $d){
				if(!isset($max[$k])) $max[$k] = 0;
				if(!isset($rand[$k])) $rand[$k] = []; 		
				$rand[$k][$p] = $max[$k] + $d[0];
				$max[$k] += $d[0];
 			}
		}
		$this->rand = [$rand,$max];
	}

	public function saveYml(){
		$this->set->save();
		$this->mb->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}