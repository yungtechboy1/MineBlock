<?php
namespace MineBlock\FastBurn;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;

class FastBurn extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onFuranceBurn(FurnaceBurnEvent $event){
		$event->getFurnace()->namedtag["CookTime"] = 200; 
	}


	public function onFuranceSmelt(FurnaceSmeltEvent $event){
		$event->getFurnace()->namedtag["BurnTime"] = 0;
		$event->getFurnace()->namedtag["CookTime"] = 200; 
	}
}