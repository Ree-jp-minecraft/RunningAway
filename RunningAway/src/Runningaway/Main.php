<?php

namespace Runningaway;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class Main extends pluginBase implements Listener {
    
    public function itemNbt($p , $item){
        if($item->getId() == 0)return true;
        $n = $p->getName();
        
        switch($item->getId()){
            
        case "388":
            if($this->gamemode[$n] == "run"){
                $item = $p->getInventory()->getItemInHand();
				$item->setCount($item->getCount() - 1);
				$p->getInventory()->setItemInHand($item);
                $p->addEffect(new EffectInstance(Effect::getEffect(1), 200, 1, true));
            }else{
                $p->sendMessage("§bsystem>>§rこのアイテムは逃走者のみ使えます");
            }
            return true;
            
        case "138":
            $this->menu($p);
            return true;
        }
    }
    
    public function onEnable(){
	    $this->getServer()->getPluginManager()->registerEvents($this, $this);
	    $this->getLogger()->info("loadingnow...");

if(!file_exists($this->getDataFolder())){
           mkdir($this->getDataFolder(),0774,true);
        }
	   $this->ucoin = new Config($this->getDataFolder()."user_coin.yml",Config::YAML);
	   
if(!file_exists($this->getDataFolder())){
           mkdir($this->getDataFolder(),0774,true);
        }
	   $this->oprank = new Config($this->getDataFolder()."oprank.yml",Config::YAML);
	   
	   $this->game = false;
	   $this->gameTime = 30;
	   $this->getServer()->loadLevel("map1");
	   $this->getScheduler()->scheduleRepeatingTask(new GameManager($this),20);
	   $this->getScheduler()->scheduleRepeatingTask(new nameTag($this),100);
	   $this->needoprank = 0;
	   
    }
    
    public function onReceive(DataPacketReceiveEvent $ev){
		$p = $ev->getPlayer();
		$pk = $ev->getPacket();
		$n = $p->getName();
		
		if($pk->getName() == "ModalFormResponsePacket"){
		    $data = json_decode($pk->formData);
		    
		    if($data === null){
		        return;
		    }
		    
		    if($data !== null){
		    switch($pk->formId){
		        
		        case 00000:
		            switch($data){
		                case 0:
						$buttons[] = [
						'text' => "スピード(使用すると10秒間スピードが上がります)§a1000gold§r"]; 
						$buttons[] = [
						'text' => "実装をお待ちください(form作るのだるい)"]; 
						$come = "";

						$this->sendForm($p ,$buttons ,"§l§bMenu>>§cShop§r" , $come,10000);
						break;

	                    case 1:
						$p->sendMessage("実装までお待ちください");
	                    break;

						case 2:
						$p->sendMessage("実装までお待ちください");
	                    break;

						case 3:
						$p->sendMessage("実装までお待ちください");
	                    break;
	    
		            }
		        break;
		        
		        case 10000:
		            switch($data){
		                case 0:
						 $come = "";
						 $buttons[] = ["text" => "買う"];
						 $buttons[] = ["text" => "やめる"];
						 $this->sendForm($p ,$buttons ,"§l§bMenu>>§cShop§r" , $come,10001);
		                break;

						case 1:
						$p->sendMessage("実装までお待ちください");
						break;
		            }
				break;
				
				case 10001:
				    
		            switch($data){
		                    
						case 0:
						$coin = $this->coin[$n];
	                    $this->coin[$n] = $coin - 1000;
	                    $coin = $this->coin[$n];
	                    $this->ucoin->set($n,$coin);
	                    $this->ucoin->save();
						$come = "1000coinでアイテムを購入しました\n現在の所持金".$this->coin[$n];
						$buttons[] = ["text" => "閉じる"];
						$buttons[] = ["text" => "Meunに戻る"];
						 $this->sendForm($p ,$buttons ,"§l§cMenu>>§bShop§r" , $come,10002);
						$item = Item::get(388, 0, 1);
						$item->setCustomName("§l§aSpeed§r");
						$p->getInventory()->addItem($item);
						break;
					
						case 1:
						
						break;
		            }

				break;
				
				case 10002:
				
				    switch($data){
				        case 0:
				        break;
				        
				        case 1:
				        $this->menu($p);
				        break;
				    }
		            }
		        
		    }
		    
		}
    }

	public function sendForm($p ,$buttons ,$title ,$come ,$id){
		$data = [ 
		'type'    => 'form',
		'title'   => $title, 
		'content' => $come,
		'buttons' => $buttons
		]; 
		$pk = new ModalFormRequestPacket(); 
		$pk->formId = $id;
		$pk->formData = json_encode( $data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE );
	    $p->dataPacket($pk);

	}
    
    public function onPreLogin (PlayerPreLoginEvent $ev){
        $p = $ev->getPlayer();
	    $n = $p->getName();
	    
	    $needrank = $this->needoprank;
	    $oprank = $this->oprank->get($n);
        if($oprank < $needrank){
            
            $p->kick("§bsystem>>§rサーバーに接続するための権限が足りません\n§bsystem>>§ryour op rank ".$oprank."\n§bsystem>>§rnecessary oprank ".$needrank,false);
        }
    }
	
	public function onLogin(PlayerLoginEvent $ev){
	    $p = $ev->getPlayer();
	    $n = $p->getName();
	    
	    
	    //プレイヤーの初ログイン処理
	    if(!$this->oprank->exists($n)){
	        $this->ucoin->set($n,"1000");
	        $this->oprank->set($n,1);
	        
	        $this->ucoin->save();
	        $this->oprank->save();
	    }
	    
	    
	    
	    $this->coin[$n] = $this->ucoin->get($n);
	    $this->name[$n] = $p;
	    
	}
	
	public function onJoin(PlayerJoinEvent $ev){
	    
	    $p = $ev->getPlayer();
	    $n = $p->getName();
	    
	    $needrank = $this->needoprank;
	    $oprank = $this->oprank->get($n);
	    if($oprank < $needrank) {
	        $p->kick("§bsystem>>§rサーバーに接続するための権限が足りません\n§bsystem>>§ryour op rank ".$oprank."\n§bsystem>>§rnecessary oprank ".$needrank, true);
	    }else{
	    
	    
	    
	    //プレイヤーを待機状態に
	    $p->setNameTagAlwaysVisible(true);
	    $this->gamemode[$n] = "lobby";
	    $p->setNameTag("[§5lobby§r]".$n);
        $p->setDisplayName("[§5lobby§r]".$n);
        $x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getX();
		$y = $this->getServer()->getDefaultLevel()->getSafeSpawn()-> getY();
		$z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();
		$level = $this->getServer()->getDefaultLevel();
		$p->setLevel($level);
		$p->teleport(new Vector3($x, $y, $z, $level));
		
		$item = Item::get(138, 0, 1);
		$item->setCustomName("§r§l§cMenu§r");
		
		if(!$p->getInventory()->contains($item)){
			if($p->getInventory()->canAddItem($item)){
				$p->getInventory()->addItem($item);
			}
		}
		
		if($oprank > 999){
		    $p->setGamemode("0");
		}
		
		$p->sendMessage("§bsystem>>§alogin完了。あなたの所持金は".$this->coin[$n]);
	    }
	}
	
	public function onquit(PlayerQuitEvent $ev){
	    //playerdetaの保存
	    $p = $ev->getPlayer();
	    $n = $p->getName();
	    $coin = $this->coin[$n];
	    $this->ucoin->set($n,$coin);
	    $this->ucoin->save();
	}
	
	public function onTuch(PlayerInteractEvent $ev){
	    $p = $ev->getPlayer();
	    $item = $ev->getItem();
	    
	    if(!$this->itemNbt($p,$item)){
	        $p->sendMessage("§4systemerrer>>Unable to get item item data");
	    }
	}
	
	public function onBreak(BlockBreakEvent $ev){
	    
	    $p = $ev->getPlayer();
	    $n = $p->getName();
	    $oprank = $this->oprank->get($n);
	    
	    if($oprank < 50){
	    
	    $breakxyz = $ev->getblock()->asVector3();
	    $level = $p->getlevel();
    
	    $particle = new FloatingTextParticle($breakxyz, "§bsystem>>§aYou do not have permission to edit the world");
        $level->addParticle($particle);
    
        $this->getScheduler()->scheduleDelayedTask(new particle($particle,$level), 20);
	    $ev->setCancelled();
	    }
	}
	
	public function onattack(EntityDamageEvent $ev){
	    
	    if($ev instanceof EntityDamageByEntityEvent){
	        $atkedp = $ev->getEntity();
	        $damagep = $ev->getDamager();
	        $atkname = $atkedp->getName();
	        $damagename = $damagep->getName();
	        $time = $this->gameTime;
	        
	        if($time < 270){
	            
	        var_dump($this->gamemode[$damagename]);
	        if($this->gamemode[$atkname]== "run"){
	            
	            if($this->gamemode[$damagename] == "hunter"){
	                
	            $this->gamemode[$atkname] = "lobby";
	            $atkedp->setNameTag("[§5lobby§r]".$atkname);
                $atkedp->setDisplayName("[§5lobby§r]".$atkname);
                $atkedp->setNameTagAlwaysVisible(true);
                
                $atkedp->sendMessage("§bsystem>>§4".$damagename."に逮捕された");
                
                $damagep->sendMessage("§bsystem>>§4".$atkname."を逮捕した");
                $this->givecoin($damagename,"3000");
	        }
	    }
	    }
	}
	$ev->setCancelled();
	}
	
	public function givecoin ($n,$addcoin){
	    $p = $this->name[$n];
	    $coin = $this->coin[$n];
	    $this->coin[$n] = $addcoin+$coin;
	    $coin = $this->coin[$n];
	    $this->ucoin->set($n,$coin);
	    $this->ucoin->save();
	    $p->sendMessage("§bsystem>>§a+".$addcoin."coin");
	}
	
	public function menu($p){
	    
	    $buttons[] = [
			'text' => "ショップ"
			]; 
		$buttons[] = [
			'text' => "おみくじ"
			]; 
		$buttons[] = [
			'text' => "エンダーチェスト"
			]; 
		$buttons[] = [
			'text' => "復活"
			]; 

		$this->sendForm($p ,$buttons ,"§l§bsystem>>§cMenu§r" ,"" ,00000);

	}
	
	public function onCommand(CommandSender $p, Command $cmd, string $label, array $args) :bool{
		switch (strtolower($cmd->getName())) {
		    case "console":
		        if(!isset($args[0]))return false;
		  
				switch(strtolower($args[0])){
				    case "oprank":
				        if(is_int($args[1])){
				            $this->needoprank = $args[1];
				            $p->sendMassage("§bsystem>>§rサーバーに接続ための権限を".$args[1]."に制限しました");
				            return true;
				        }
				        return false;
				    
				    case "logout":
	                    $n = $p->getName();
	                    $coin = $this->coin[$n];
	                    $this->ucoin->set($n,$coin);
	                    $this->ucoin->save();
	                    $p->kick("§bsystem>>§rLogout完了",false);               
				    
				    
				    case "start":
						    $ps = $this->getServer()->getOnlinePlayers();
				        if(count($ps) < 5){
				            $p->sendMessage("§bsystem>>§aプレイヤーが5人未満のためゲームを始められませんでした");
				            
				        }
				        
				        Server::getInstance()->broadcastMessage($p."がゲームを始めました");
				        
                        $players = [];
                        $num = 0;
                        foreach ($ps as $player) {
                            $this->ps[$num] = $player;
                            $num++;
                        }
                        $this->run = $this->ps;
                        $pn = count($ps) - 1;
				        $r = rand(0,$pn);
				        
                        $hunter[] = $this->ps[$r];
                        unset($this->run[$r]);
                        
                        foreach($hunter as $hun){
                        $hunn = $hun->getName();
                        $this->gamemode[$hunn] = "hunter";
                        $hun->sendMessage("§bsystem>>§4あなたが鬼に選ばれました");
                        $hun->setNameTag("[§4hunter§r]".$hunn);
                        $hun->setDisplayName("[§4hunter§r]".$hunn);
                        Server::getInstance()->broadcastMessage("§e".$hunn."が鬼になりました");
                        }
                        
                        if(isset($this->run)){
                        
                        foreach($this->run as $runner){
                            $runname = $runner->getName;
                            $this->gamemode[$runname] = "run";
                            $runner->setNameTag("[§9run§r]".$runname);
                            $runner->setDisplayName("[§9run§r]".$runname);
                            $runner->sendMessag("§bsystem>>§9あなたは逃走者です");
                            $runner->setNameTagAlwaysVisible(false);
                        }
                        }
                        
                        $this->game = "true";
                        $this->gameTime = 300;
                        $l = $this->getServer()->getLevelByName("map1");
                        
                        foreach($this->run as $runner){
                            $runner->setLevel($l);
                            $runner->teleport(new Vector3(5, 69, 7, $l));
                        }
                        Server::getInstance()->broadcastMessage("§bsystem>>§eゲームを始めます\n§bsystem>>§rハンターは30秒後にテレポートされます");
                        $this->getScheduler()->scheduleDelayedTask(new hunter($hunter,$l),600);
                        
                        
                        

                        
                        return true;
				    
				    
				    case "help":
				        $p->sendMessage("§e-----RunningAwayOpHelp-----");
				        $p->sendMessage("§e/console givecoin <player> <coin>");
				        $p->sendMessage("§eプレイヤーにコインを与える");
				        $p->sendMessage(" ");
				        $p->sendMessage("§e/console start");
				        $p->sendMessage("§eゲームをスタートさせる");
				        $p->sendMessage(" ");
				        $p->sendMessage("§e/console logout");
				        $p->sendMessage("§eログアウトする");
				        $p->sendMessage(" ");
				        $p->sendMessage("§e/console oprank <rank(int)>");
				        $p->sendMessage("§eサーバーに接続できるプレイヤーを制限する");
				        $p->sendMessage("§e--------------------------");
				        return true;
				        
				    
				    case "givecoin":
				        if(!isset($args[1]))return false;
				        if(!isset($args[2]))return false;
				        
				        $this->givecoin($args[1],$args[2]);
				        $p->sendMessage("§bsystem>>§r".$args[1]."に".$args[2]."coinを与えました");
				        return true;
				        
				    case "menu":
				        $this->menu($p);
				        return true;
				}
		    }
	}
	
}