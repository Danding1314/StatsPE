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

namespace Fadhel\StatsPE\utils;

use Fadhel\StatsPE\Main;
use Fadhel\StatsPE\utils\scoreboard\Action;
use Fadhel\StatsPE\utils\scoreboard\DisplaySlot;
use Fadhel\StatsPE\utils\scoreboard\Scoreboard;
use Fadhel\StatsPE\utils\scoreboard\Sort;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class Runtime extends Task
{
    protected $plugin;
    protected $player;
    protected $score;

    /**
     * Runtime constructor.
     * @param Main $plugin
     * @param Player $player
     */
    public function __construct(Main $plugin, Player $player)
    {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->score = new Scoreboard(TextFormat::colorize($this->plugin->getConfig()->get("title")), Action::CREATE);
        $this->score->create(DisplaySlot::SIDEBAR, Sort::DESCENDING);
    }

    public function onRun(int $currentTick)
    {
        $lines = $this->plugin->getConfig()->get("lines");
        $i = 15 - count($lines);
        if (count($lines) > 15 || count($lines) < 1) {
            $this->plugin->getLogger()->error("Lines amount must be between the value of 1-15. " . count($lines) . " is out of range");
            return;
        }
        foreach ($lines as $line) {
            $this->score->setLine($this->player, $i, TextFormat::colorize(str_replace(["{kills}", "{deaths}", "{streak}", "{best_streak}", "{kdr}"], [$this->plugin->getKills($this->player->getName()), $this->plugin->getDeaths($this->player->getName()), $this->plugin->getStreak($this->player->getName()), $this->plugin->getBestStreak($this->player->getName()), $this->plugin->getKDR($this->player->getName())], $line)));
            $i++;
        }
        $this->score->addDisplay($this->player);
    }
}
