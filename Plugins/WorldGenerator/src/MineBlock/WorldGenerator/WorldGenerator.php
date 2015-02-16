<?php
namespace MineBlock\WorldGenerator;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;

class WorldGenerator extends PluginBase{

	public function onLoad(){
		Generator::addGenerator(NoneGN::class, "None");
		Generator::addGenerator(OreFlatGN::class, "OreFlat");
		Generator::addGenerator(SkyBlockGN::class, "SkyBlock");
		Generator::addGenerator(SkyGridGN::class, "SkyGrid");
	}
}