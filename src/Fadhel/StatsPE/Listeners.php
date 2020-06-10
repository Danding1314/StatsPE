<?php

/**
 * Copyright 2020 Fadhel
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Fadhel\StatsPE;

use Fadhel\StatsPE\utils\Runtime;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Player;

class Listeners implements Listener
{
    protected $plugin;

    /**
     * Listeners constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer()->getName();
        if (!$this->plugin->hasAccount($player)) {
            $this->plugin->createAccount($player);
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        if ($this->plugin->getConfig()->get("enable-scoreboard")) {
            $this->plugin->getScheduler()->scheduleRepeatingTask(new Runtime($this->plugin, $event->getPlayer()), 20);
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $this->plugin->syncBest($player->getName());
        $this->plugin->resetStreak($player->getName());
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            $killer = $cause->getDamager();
            if ($killer instanceof Player) {
                $this->plugin->addKill($killer->getName());
                $this->plugin->addStreak($killer->getName());
                $this->plugin->syncBest($killer->getName());
            }
        }
    }
}
