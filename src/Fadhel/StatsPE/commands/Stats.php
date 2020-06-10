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

namespace Fadhel\StatsPE\commands;

use Fadhel\StatsPE\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class stats extends Command
{
    protected $plugin;

    /**
     * stats constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("stats", "Shows your/others status", "Usage /stats <player>", []);
    }

    /**
     * @param CommandSender $sender
     * @param string $player
     */
    private function sendStats(CommandSender $sender, string $player): void
    {
        $sender->sendMessage(TextFormat::colorize(str_replace(["{player}", "{kills}", "{deaths}", "{streak}", "{best_streak}", "{kdr}"], [$this->plugin->getAccountName($player), $this->plugin->getKills($player), $this->plugin->getDeaths($player), $this->plugin->getStreak($player), $this->plugin->getBestStreak($player), $this->plugin->getKDR($player)], $this->plugin->getConfig()->get("stats-message"))));
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (empty($args[0])) {
            if ($sender instanceof Player) {
                $this->sendStats($sender, $sender->getName());
            } else {
                $sender->sendMessage(TextFormat::RED . $this->getUsage());
            }
        } else {
            if ($this->plugin->hasAccount($args[0])) {
                $this->sendStats($sender, $args[0]);
            } else {
                $sender->sendMessage(TextFormat::colorize(str_replace("{player}", $args[0], $this->plugin->getConfig()->get("error-message"))));
            }
        }
    }
}
