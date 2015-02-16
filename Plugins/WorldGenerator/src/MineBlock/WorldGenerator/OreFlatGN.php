<?php
namespace MineBlock\WorldGenerator;

use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GenerationChunkManager;
use pocketmine\block\Block;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\utils\Random;

class OreFlatGN extends Generator{
	private $level;
	private $chunk;
	private $random;
	private $populators = [];
	private $structure, $chunks, $options, $floorLevel, $preset;
	public $maxHeight = 34;

	public function getSettings(){
		return $this->options;
	}

	public function getName(){
		return "oreflat";
	}

	public function __construct(array $options = []){
	}

	public function init(GenerationChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;
		$this->random->setSeed($this->level->getSeed());
		$this->noiseHills = new Simplex($this->random, 3, 0.1, 12);
		$this->noiseBase = new Simplex($this->random, 16, 0.6, 16);
		$ores = new Ore();
		$a = $this->maxHeight / 128;
		$ores->setOreTypes([
			new OreType(new CoalOre(), 20, 16, 0, 128 * $a),
			new OreType(New IronOre(), 20, 8, 0, 64 * $a),
			new OreType(new RedstoneOre(), 8, 7, 0, 16 * $a),
			new OreType(new LapisOre(), 1, 6, 0, 32 * $a),
			new OreType(new GoldOre(), 2, 8, 0, 32 * $a),
			new OreType(new DiamondOre(), 1, 7, 0, 16 * $a),
			new OreType(new Dirt(), 20, 32, 0, 128 * $a),
			new OreType(new Gravel(), 10, 16, 0, 128 * $a),
		]);
		$this->populators[] = $ores;
		$trees = new Tree();
		$trees->setBaseAmount(1);
		$trees->setRandomAmount(1);
		$this->populators[] = $trees;
		$tallGrass = new TallGrass();
		$tallGrass->setBaseAmount(5);
		$tallGrass->setRandomAmount(0);
		$this->populators[] = $tallGrass;
	}

	public function generateChunk($chunkX, $chunkZ){
		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$list = [0 => 7, ($this->maxHeight-1) => 3, $this->maxHeight => 2];
		for($x = 0; $x < 16; $x++){
			for($y = 0; $y <= $this->maxHeight; $y++){
				for($z = 0; $z < 16; $z++){
					$chunk->setBlockId($x, $y, $z, isset($list[$y]) ? $list[$y] : 1);
				}
			}
		}
		$this->random->setSeed((int) (($chunkX * 0xdead + $chunkZ * 0xbeef) * $this->floatSeed));
	}

	public function populateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach($this->populators as $populator){
			$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}

	public function getSpawn(){
		return $this->level->getSafeSpawn(new Vector3(127.5, 128, 127.5));
	}
}