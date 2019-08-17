<?php

namespace Runningaway;

use pocketmine\scheduler\Task;
use pocketmine\server;
use pocketmine\level\particle\FloatingTextParticle;

class particle extends Task{
    
public function __construct($particle,$level){
        $this->particle = $particle;
        $this->level = $level;
    }

public function onRun(int $tick){
    $this->particle->setInvisible(true);
    $this->level->addParticle($this->particle);
}

}