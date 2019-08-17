<?php

namespace Runningaway;

use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

class GameManager extends Task{
	public function __construct(Main $main){
		$this->m = $main;
	}
	
	public function onRun(int $tick){
		$m = $this->m;
		$time = $m->gameTime;
		$time--;
		$m->gameTime = $time;
		
		if($m->game == true){
		    
		    $m->gameTime = $time;
		    $ps = $m->getServer()->getOnlinePlayers();
		    
		    $run = 0;
		    $hunter = 0;
		    
		    foreach($ps as $p){
		        if(isset($m->gamemode[$p->getName()])){
		            
		            if($m->gamemode[$p->getName()] == "run"){
		            $run++;
		        }
		            if($m->gamemode[$p->getName()] == "hunter"){
		            $hunter++;
		        }
		            
		        }
		        
		        
		        
		        $p->addActionBarMessage("§bsystem>>§rゲーム終了まで".$time,0,15,0);
		        
		        
		    }
		    if($run == 0){
		            $m->gameTime = -1;
		            Server::getInstance()->broadcastMessage("§bsystem>>§4逃走者が全滅しました");
		        }
		    if($hunter == 0){
		            $m->gameTime = -1;
		            Server::getInstance()->broadcastMessage("§bsystem>>§4ハンターが全滅しました");
		        }
		    
		    if($m->gameTime < 0){
		        $m->game = false;
		        Server::getInstance()->broadcastMessage("§bsystem>>§rゲームが終了しました");
		        
		        foreach($ps as $p){
		            $n = $p->getName();
		            $m->gamemode[$n] = "lobby";
		            $p->setNameTag("[§5lobby§r]".$n);
		            $p->setDisplayName("[§5lobby§r]".$n);
		            $p->setNameTagAlwaysVisible(true);
		            
		            $x = $m->getServer()->getDefaultLevel()->getSafeSpawn()->getX();
		            $y = $m->getServer()->getDefaultLevel()->getSafeSpawn()-> getY();
		            $z = $m->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();
		            $level = $m->getServer()->getDefaultLevel();
		            $p->setLevel($level);
		            $p->teleport(new Vector3($x, $y, $z, $level));
		            $m->gameTime = 30;
		        }
		    }
		    
		}elseif($m->game == false){
		    if($m->gameTime >0){
		        
		        foreach($m->getServer()->getOnlinePlayers() as $p){
		            $p->addActionBarMessage("§bsystem>>§rゲームが始まるまで".$m->gameTime,0,15,0);
		        }
		        
		    }elseif($m->gameTime == 0){

		        $ps = [];
		        $num = 0;
                foreach ($m->getServer()->getOnlinePlayers() as $p) {
                    $n = $p->getName();
                    if($m->gamemode[$n] = "lobby"){
                        $ps[$num] = $p;
                        $num++;
                    }
                }
		        
		        if(1 < count($ps)){
		            
                    $run = $ps;
                    $pn = count($ps) - 1;
                    $hunter = [];
                    
				    $r = rand(0,$pn);
				    
//ハンター抽選
				    for($hn = ceil($pn / 3) ;$hn-- ;$hn < 1){
				        $pn = count($run) - 1;
				        $r = rand(0,$pn);
				        $hunter[$hn] = $run[$r];
				        unset($run[$r]);
				    }
                        
                        foreach($hunter as $hun){
                        $hunn = $hun->getName();
                        $m->gamemode[$hunn] = "hunter";
                        $hun->sendMessage("§bsystem>>§4あなたが鬼に選ばれました");
                        $hun->setNameTag("[§4hunter§r]".$hunn);
                        $hun->setDisplayName("[§4hunter§r]".$hunn);
                        Server::getInstance()->broadcastMessage("§bsystem>>§e".$hunn."が鬼になりました");
                        }
                        
                        
                        foreach($run as $runner){
                            $runname = $runner->getName();
                            $m->gamemode[$runname] = "run";
                            $runner->setNameTag("[§9run§r]".$runname);
                            $runner->setDisplayName("[§9run§r]".$runname);
                            $runner->sendMessage("§bsystem>>§9あなたは逃走者です");
                            $runner->setNameTagAlwaysVisible(false);
                        }
                        
                        $m->game = true;
                        $m->gameTime = 300;
                        $l = $m->getServer()->getLevelByName("map1");
                        
                        foreach($run as $runner){
                            $runner->setLevel($l);
                            $runner->teleport(new Vector3(5, 69, 7, $l));
                        }
                        Server::getInstance()->broadcastMessage("§bsystem>>§eゲームを始めます\n§bsystem>>§rハンターは30秒後にテレポートされます");
                        $m->getScheduler()->scheduleDelayedTask(new hunter($hunter,$l),600);
		        }elseif(0 < count($ps)){
		            Server::getInstance()->broadcastMessage("§bsystem>>§r人数が足りないためゲームを開始出来ませんでした");
		            $m->gameTime = 30;
		        }else{
		            $m->gameTime = 30;
		        }
		    }else{
		        Server::getInstance()->broadcastMessage("§4systemERROR>>gameManagerERROR");
		    }
		}
	}
}