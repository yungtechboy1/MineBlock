<?php
namespace MineBlock\Point;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\scheduler\CallbackTask;

class Point extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"onTick"]), 60);
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$mm = "[Point] ";
		$rm = TextFormat::RED . "Usage: /";
		$pt = $this->pt;
		$ik = $this->isKorean();
		$n = $sender->getName();
		$c = false;
		switch(strtolower($cmd->getName())){
			case "point":
				$rm .= "Point ";
				switch(strtolower($sub[0])){
					case "me":
					case "my":
					case "m":
					case "내점수":
					case "나":
						$r = $mm . ($ik ? "나의 점수 : ": "Your Point : ") . $this->getPoint($n) . ($ik ? "점  ,  랭킹 : ": "point  ,  Rank : ") . $this->getRank($n);
					break;
					case "see":
					case "view":
					case "v":
					case "보기":
						if(!isset($sub[1])){
							$r = $rm . ($ik ? "보기 <플레이어명>": "View(V) <PlayerName>");
						}elseif(!($p = $this->getPlayer($sub[1]))){
							$r = $mm . $sub[1] . ($ik ? "은 잘못된 이름입니다.": " is invalid name");
						}else{
							$r = $mm . $p . ($ik ? "의 점수 : ": "'s Point : ") . $this->getPoint($p) . ($ik ? "점  ,  랭킹 : ": "point ,  Rank : ") . $this->getRank($p);
						}
					break;
					case "rank":
					case "r":
					case "랭킹":
					case "순위":
						if(isset($sub[1]) && is_numeric($sub[1]) && $sub[1] > 1){
							$r = $this->getRanks(round($sub[1]));
						}else{
							$r = $this->getRanks(1);
						}
					break;
					default:
						return false;
					break;
				}
			break;
			case "pointop":
				$rm .= "PointOP ";
				switch(strtolower($sub[0])){
					case "set":
					case "s":
					case "설정":
						if(!isset($sub[1])){
							$r = $rm . ($ik ? "설정 <플레이어명> <점수>": "Set(S) <PlayerName> <Point>");
						}elseif(!($p = $this->getPlayer($sub[1]))){
							$r = $mm . $sub[1] . ($ik ? "은 잘못된 이름입니다.": " is invalid name");
						}elseif(!is_numeric($sub[2]) || $sub[2] < 0){
							$r = $mm . $sub[2] . ($ik ? "은 잘못된 숫자입니다.": " is invalid number");
						}else{
							$sub[2] = $sub[2] < 0 ? 0: floor($sub[2]);
							$this->setPoint($p, $sub[2]);
							$r = $mm . $p . ($ik ? "의 점수을 $sub[2] 점으로 설정했습니다.  ": "'s point is set to $sub[2] $");
							if($player = $this->getServer()->getPlayerExact($p)) $player->sendMessage($mm . ($ik ? "당신의 점수이 어드민에 의해 변경되었습니다. 나의 점수 : ": "Your point is change by admin. Your point : ") . $this->getPoint($p) . ($ik ? "점": "point"));
						}
					break;
					case "give":
					case "g":
					case "지급":
						if(!isset($sub[1])){
							$r = $rm . ($ik ? "지급 <플레이어명> <점수>": "Give(G) <PlayerName> <Point>");
						}elseif(!($p = $this->getPlayer($sub[1]))){
							$r = $mm . $sub[1] . ($ik ? "은 잘못된 이름입니다.": " is invalid name");
						}elseif(!is_numeric($sub[2]) || $sub[2] < 0){
							$r = $mm . $sub[2] . ($ik ? "은 잘못된 숫자입니다.": " is invalid number");
						}else{
							$sub[2] = $sub[2] < 0 ? 0: floor($sub[2]);
							$this->givePoint($p, $sub[2]);
							$r = $mm . ($ik ? "$p 님에게 $sub[2] 점을 지급햇습니다. ": "Give the $sub[2] point to $p");
						}
					break;
					case "take":
					case "t":
					case "뺏기":
						if(!isset($sub[1])){
							$r = $rm . ($ik ? "뺏기 <플레이어명> <점수>": "Take(T) <PlayerName> <Point>");
						}elseif(!($p = $this->getPlayer($sub[1]))){
							$r = $mm . $sub[1] . ($ik ? "은 잘못된 이름입니다.": " is invalid name");
						}elseif(!is_numeric($sub[2]) || $sub[2] < 0){
							$r = $mm . $sub[2] . ($ik ? "은 잘못된 숫자입니다.": " is invalid number");
						}else{
							$sub[2] = $sub[2] < 0 ? 0: floor($sub[2]);
							$this->takePoint($p, $sub[2]);
							$r = $mm . ($ik ? "$p 님에게서 $sub[2] 점을 빼앗았습니다. ": "Take the $sub[2] point to $p");
						}
					break;
					break;
					case "clear":
					case "c":
					case "초기화":
						foreach($pt["Point"] as $k => $v)
							$pt["Point"][$k] = 0;
						$m = $mm . ($ik ? "모든 플레이어의 점수가 초기화되었습다.": "All Player's point is reset");
						$c = true;
					break;
					case "nick":
					case "n":
					case "닉네임":
						$pt["Nick"] = !$pt["Nick"];
						$m = $mm . ($ik ? "닉네임 모드를 " . ($pt["Nick"] ? "켭": "끕") . "니다.": "MickName mode is " . ($pt["Nick"] ? "On": "Off"));
						$c = true;
					break;
					case "op":
					case "o":
					case "오피":
						$pt["OP"] = !$pt["OP"];
						$m = $mm . ($ik ? "오피를 랭킹에 포함" . ($pt["OP"] ? "": "안") . "합니다.": "Show on rank the Op is " . ($pt["OP"] ? "On": "Off"));
						$c = true;
					break;
					default:
						return false;
					break;
				}
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		if(isset($m)) $this->getServer()->broadcastMessage($m);
		if($c && $this->pt !== $pt) $this->pt = $pt;
		return true;
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		$n = strtolower($event->getPlayer()->getName());
		if(!isset($this->pt["Point"][$n])){
			$this->pt["Point"][$n] = 0;
			$this->saveYml();
		}
	}

	public function getMP($name = ""){
		if(!$name) return false;
		$name = strtolower($name);
		foreach($this->pt["Point"] as $k => $v){
			if($k == $name){
				$get = ["Player" => $k,"Point" => $v];
				break;
			}
		}
		if(!isset($get)){
			foreach($this->pt["Point"] as $k => $v){
				if(strpos($k, $name) === 0){
					$get = ["Player" => $k,"Point" => $v];
					break;
				}
			}
		}
		return isset($get) ? $get: false;
	}

	public function getPlayer($name = ""){
		if(!$this->getMP($name)) return false;
		else return $this->getMP($name)["Player"];
	}

	public function getPoint($name = ""){
		if(!$this->getMP($name)) return false;
		else return $this->getMP($name)["Point"];
	}

	public function hasPoint($name = "", $point = 0){
		if(!$m = $this->getPoint($name)) return false;
		else return $point <= $m;
	}

	public function setPoint($name = "", $point = 0){
		$pt = $this->pt["Point"];
		$name = strtolower($name);
		if(!is_numeric($point) || $point < 0) $point = 0;
		if(!$name && !$all && !$this->getPoint($name)){
			return false;
		}else{
			$pt[strtolower($name)] = floor($point);
		}
		if($this->pt["Point"] !== $pt){
			$this->pt["Point"] = $pt;
			$this->saveYml();
		}
		return true;
	}

	public function givePoint($name = "", $point = 0){
		if(!is_numeric($point) || $point < 0) $point = 0;
		if(!$name && !$all && !$this->getPoint($name)){
			return false;
		}else{
			$this->setPoint($name, $this->getPoint($name) + $point);
		}
		return true;
	}

	public function takePoint($name = "", $point = 0){
		if(!is_numeric($point) || $point < 0) $point = 0;
		if(!$name && !$all && !$this->getPoint($name)){
			return false;
		}else{
			$getPoint = $this->getPoint($name);
			if($getPoint < $point) $point = $getPoint;
			$this->setPoint($name, $this->getPoint($name) - $point);
		}
		return true;
	}

	public function getRanks($page = 1){
		$m = $this->pt["Point"];
		arsort($m);
		$ik = $this->isKorean();
		$list = ceil(count($m) / 5);
		if($page >= $list) $page = $list;
		$r = "[Rank] (" . ($ik ? "페이지": "Page") . " $page/$list) \n";
		$num = 1;
		foreach($m as $k => $v){
			if(!$this->pt["OP"] && $this->getServer()->isOp($k)) continue;
			if(!isset($same)) $same = [$v,$num];
			if($v == $same[0]){
				$rank = $same[1];
			}else{
				$rank = $num;
				$same = [$v,$num];
			}
			if($num + 5 > $page * 5 && $num <= $page * 5) $r .= "  [" . ($v > 0 ? $rank: "-") . "] $k : $v \n";
			$num++;
		}
		return $r;
	}

	public function getRank($name = ""){
		if(!$name) return false;
		$m = $this->pt["Point"];
		arsort($m);
		$rank = 0;
		foreach($m as $k => $v){
			$rank++;
			if($k == strtolower($name)) return !$this->pt["OP"] && $this->getServer()->isOp($k) ? "OP": ($v > 0 ? $rank: "-");
		}
		return false;
	}

	public function onTick(){
		if(!isset($this->nick)) $this->nick = true;
		if(!$this->nick && !$this->pt["Nick"]) return;
		$pt = $this->pt["Point"];
		arsort($pt);
		foreach($pt as $k => $v){
			if(!($p = $this->getServer()->getPlayerExact($k))) continue;
			$n = $p->getDisplayName();
			$nt = $this->pt["Nick"] ? str_replace(["%name","%rank","%point"], [$n,$this->getRank($k),$v], $this->pt["Nick_Format"]): $n;
			if($p->getNameTag() !== $nt) $p->setNameTag($nt);
		}
		$this->nick = $this->pt["Nick"];
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! MineBlock/");
		$this->pt = (new Config($this->getServer()->getDataPath() . "/plugins/! MineBlock/" . "Point.yml", Config::YAML, ["Point" => [],"Nick" => true,"Nick_Format" => "[%rank] %name : %point","OP" => true]))->getAll();
	}

	public function saveYml(){
		asort($this->pt);
		$pt = new Config($this->getServer()->getDataPath() . "/plugins/! MineBlock/" . "Point.yml", Config::YAML);
		$pt->setAll($this->pt);
		$pt->save();
	}

	public function isKorean(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! MineBlock/");
		if(!isset($this->ik)) $this->ik = (new Config($this->getServer()->getDataPath() . "/plugins/! MineBlock/" . "! Korean.yml", Config::YAML, ["Korean" => false]))->get("Korean");
		return $this->ik;
	}
}