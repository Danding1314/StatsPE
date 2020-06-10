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

use Fadhel\StatsPE\commands\stats;
use pocketmine\plugin\PluginBase;
use SQLite3;

class Main extends PluginBase
{
    /** @var SQLite3 */
    public $database;

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->database = new SQLite3($this->getDataFolder() . "players.db");
        $this->database->exec("CREATE TABLE IF NOT EXISTS players(player VARCHAR(16), username VARCHAR(16), deaths INT DEFAULT 0, kills INT DEFAULT 0, streak INT DEFAULT 0, best INT DEFAULT 0)");
        $this->getServer()->getCommandMap()->register("statspe", new stats($this));
        $this->getServer()->getPluginManager()->registerEvents(new Listeners($this), $this);
    }

    /**
     * @param string $player
     * @return bool
     */
    public function hasAccount(string $player): bool
    {
        $stmt = $this->database->prepare("SELECT deaths FROM players WHERE player = :player");
        $stmt->bindValue(":player", strtolower($player));
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC)["deaths"] !== null;
    }

    /**
     * @param string $player
     */
    public function createAccount(string $player): void
    {
        $stmt = $this->database->prepare("INSERT INTO players(player, username) VALUES(:player, :username)");
        $stmt->bindValue(":player", strtolower($player));
        $stmt->bindValue(":username", $player);
        $stmt->execute();
    }

    /**
     * @param string $player
     * @return mixed
     */
    public function getAccountName(string $player)
    {
        $stmt = $this->database->prepare("SELECT username FROM players WHERE player = :player");
        $stmt->bindValue(":player", strtolower($player));
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC)["username"];
    }

    /**
     * @param string $player
     * @return mixed
     */
    public function getDeaths(string $player)
    {
        $stmt = $this->database->prepare("SELECT deaths FROM players WHERE player = :player");
        $stmt->bindValue(":player", strtolower($player));
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC)["deaths"];
    }

    /**
     * @param string $player
     * @return mixed
     */
    public function getKills(string $player)
    {
        $stmt = $this->database->prepare("SELECT kills FROM players WHERE player = :player");
        $stmt->bindValue(":player", strtolower($player));
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC)["kills"];
    }

    /**
     * @param string $player
     * @return mixed
     */
    public function getStreak(string $player)
    {
        $stmt = $this->database->prepare("SELECT streak FROM players WHERE player = :player");
        $stmt->bindValue(":player", strtolower($player));
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC)["streak"];
    }

    /**
     * @param string $player
     * @return mixed
     */
    public function getBestStreak(string $player)
    {
        $stmt = $this->database->prepare("SELECT best FROM players WHERE player = :player");
        $stmt->bindValue(":player", strtolower($player));
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC)["best"];
    }

    /**
     * @param string $player
     */
    public function addKill(string $player)
    {
        $stmt = $this->database->prepare("UPDATE players SET kills = :kills WHERE player = :player");
        $stmt->bindValue(":kills", (int)$this->getKills($player) + 1);
        $stmt->bindValue(":player", strtolower($player));
        $stmt->execute();
    }

    /**
     * @param string $player
     */
    public function addDeath(string $player)
    {
        $stmt = $this->database->prepare("UPDATE players SET death = :death WHERE player = :player");
        $stmt->bindValue(":death", (int)$this->getDeaths($player) + 1);
        $stmt->bindValue(":player", strtolower($player));
        $stmt->execute();
    }

    /**
     * @param string $player
     */
    public function addStreak(string $player)
    {
        $stmt = $this->database->prepare("UPDATE players SET streak = :streak WHERE player = :player");
        $stmt->bindValue(":streak", (int)$this->getStreak($player) + 1);
        $stmt->bindValue(":player", strtolower($player));
        $stmt->execute();
    }

    /**
     * @param string $player
     */
    public function resetStreak(string $player)
    {
        $stmt = $this->database->prepare("UPDATE players SET streak = :streak WHERE player = :player");
        $stmt->bindValue(":streak", 0);
        $stmt->bindValue(":player", strtolower($player));
        $stmt->execute();
    }

    /**
     * @param string $player
     * @param int $best
     */
    public function setBestStreak(string $player, int $best)
    {
        $stmt = $this->database->prepare("UPDATE players SET best = :best WHERE player = :player");
        $stmt->bindValue(":best", $best);
        $stmt->bindValue(":player", strtolower($player));
        $stmt->execute();
    }

    /**
     * @param string $player
     */
    public function syncBest(string $player): void
    {
        if ($this->getStreak($player) > $this->getBestStreak($player)) {
            $this->setBestStreak($player, $this->getStreak($player));
        }
    }

    /**
     * @param string $player
     * @return string
     */
    public function getKDR(string $player): string
    {
        $kills = $this->getKills($player);
        $deaths = $this->getDeaths($player);
        if ($kills !== 0) {
            if ($kills / $deaths !== 0) {
                return number_format($kills / $deaths, 1);
            }
        }
        return "0.0";
    }
}
