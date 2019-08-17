<?php

namespace Runningaway;

use pocketmine\Player;
use pocketmine\scheduler\Task;

class nameTag extends Task{
    
	public function __construct(Main $main){
		$this->main = $main;
	}
	
	public function onRun(int $tick){
	    $this->m = $this->main;
	    $ps = $this->m->getServer()->getOnlinePlayers();
	    
	    foreach($ps as $p){
	        $n = $p->getName();
	        
	        switch($n){
	            
	            case "ryosuke8639":
	                $this->gamemode($p,"§r《§bowner§r》");
	                break;
	                
	            case "takechans":
	                $this->gamemode($p,"§r《§6builder§r》");
	                break;
	        }
	            
	    }
	}
	
	public function gamemode($p,$oprank){
	    $name = $p->getName();
	    $n = $name;
	    if($name == "ryosuke8639"){
	            $n = "§aさばぬしさん§r";
	        }
	    
	    if(isset($this->m->gamemode[$name]))
	    
	    switch($this->m->gamemode[$name]){
	        
	        case "lobby":
	            $p->setNameTag($oprank."[§5lobby§r]".$n);
	            $p->setDisplayName($oprank."[§5lobby§r]".$n);
	            break;
	            
	        case "run":
	            $p->setNameTag($oprank."[§9run§r]".$n);
	            $p->setDisplayName($oprank."[§9run§r]".$n);
	            break;
	            
	        case "hunter":
	            $p->setNameTag($oprank."[§4hunter§r]".$n);
	            $p->setDisplayName($oprank."[§4hunter§r]".$n);
	            break;
	            
	    }
	}
	
}