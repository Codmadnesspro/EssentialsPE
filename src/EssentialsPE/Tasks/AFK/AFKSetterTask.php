<?php
namespace EssentialsPE\Tasks\AFK;

use EssentialsPE\BaseTask;
use EssentialsPE\Loader;
use pocketmine\utils\TextFormat;

class AFKSetterTask extends BaseTask{
    public function __construct(Loader $plugin){
        parent::__construct($plugin);
    }

    /*
     * This task is executed every 30 seconds,
     * with the purpose of checking all players' last movement
     * time, stored in their 'Session',
     * and check if it is pretty near,
     * or it's over, the default Idling limit.
     *
     * If so, they will be set in AFK mode
     */

    public function onRun($currentTick){
        $this->getPlugin()->getServer()->getLogger()->debug(TextFormat::YELLOW . "Running EssentialsPE's AFKSetterTask");
        foreach($this->getPlugin()->getServer()->getOnlinePlayers() as $p){
            if(!$this->getPlugin()->isAFK($p) && ($last = $this->getPlugin()->getLastPlayerMovement($p)) !== null && !$p->hasPermission("essentials.afk.preventauto")){
                if(time() - $last >= ($default = $this->getPlugin()->getConfig()->get("afk-auto-set")) || $default - (time() - $last) <= 15){
                    $this->getPlugin()->setAFKMode($p, true, true);
                }
            }
        }
        // Re-Schedule the task xD
        $this->getPlugin()->scheduleAutoAFKSetter();
    }
}