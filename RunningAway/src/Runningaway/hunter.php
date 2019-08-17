<?php

namespace Runningaway;

use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\math\Vector3;

class hunter extends Task{
    
public function __construct($hunter,$l){
        $this->hunter = $hunter;
        $this->l = $l;
    }

public function onRun(int $tick){
    
    foreach($this->hunter as $hun){
        $hun->setLevel($this->l);
        $hun->teleport(new Vector3(5, 69, 7, $this->l));
    }
}

}