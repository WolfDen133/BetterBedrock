<?php

declare(strict_types=1);

namespace WolfDen133\BetterBedrock;

use pocketmine\Player;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\Config;

use WolfDen133\BetterBedrock\Form\CustomForm;
use WolfDen133\BetterBedrock\Form\SimpleForm;

class Main extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $this->openMainMenu($event->getPlayer());
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $event->setCancelled();
    }

    public function openMainMenu(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                $this->openMainMenu($player);
            }
            switch ($data) {
                case "disconnect":
                    $player->close("", "Disconnected", true);
                    break;
                case "new":
                    $this->openNewServer($player);
                    break;
                case "remove":
                    $this->openRemoveServer($player);
                    break;
                case "featured":
                    $this->openFeaturedServers($player);
                    break;
                case "servers":
                    $this->openCustomServers($player);
                    break;
            }
            return;
        });
        $form->setTitle("ServerSelector");
        $form->addButton("New\nList a server", 0, "textures/ui/plus", "new");
        $form->addButton("Remove\nRemove a server", 0, "textures/ui/minus", "remove");
        $form->addButton("Featured\nOpen the featured servers", 0, "textures/ui/NetherPortal", "featured");
        $form->addButton("Custom\nYour added servers", 0, "textures/ui/world_glyph_color", "servers");
        $form->addButton("Disconnect\nLeave BetterBedrock", 0, "textures/ui/realms_red_x", "disconnect");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openCustomServers(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                $this->openMainMenu($player);
                return;
            }
            $ip = $data["ip"];
            $port = $data["port"];
            $player->transfer($ip, (int)$port, "Connecting");
            return;
        });
        $form->setTitle("Your servers");
        $config = new Config($this->getDataFolder() . $player->getName() . ".yml", Config::YAML);
        $form->setContent("Pick a server to join");
        foreach ($config->getAll() as $value) {
            if ($value !== null) $form->addButton($value["name"] . "\n" . $value["ip"] . ":" . $value["port"], 0, "textures/ui/servers", $value);
        }

        $form->sendToPlayer($player);
        return $form;
    }

    public function openFeaturedServers(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                $this->openMainMenu($player);
                return;
            }
            switch ($data) {
                case "hive":
                    $player->transfer("geo.hivebedrock.network", 19132, "Connecting");
                    break;
                case "cubecraft":
                    $player->transfer("mco.cubecraft.net", 19132, "Connecting");
                    break;
                case "galaxite":
                    $player->transfer("play.galaxite.net", 19132, "Connecting");
                    break;
                case "lifeboat":
                    $player->transfer("play.lbsg.net", 19132, "Connecting");
                    break;
                case "mineplex":
                    $player->transfer("pe.mineplex.com", 19132, "Connecting");
                    break;
                case "minevile":
                    $player->transfer("play.inpvp,net", 19132, "Connecting");
                    break;
            }
            return;
        });
        $form->setTitle("Featured Servers");
        $form->setContent("Choose a server to join");
        $form->addButton("Hive\nplay.hivemc.com:19132", 0, "textures/other/hive", "hive");
        $form->addButton("CubeCraft\nmco.cubecraft.net:19132", 0, "textures/other/cubecraft", "cubecraft");
        $form->addButton("Galaxite\nplay.galaxite.net:19132", 0, "textures/other/galaxite", "galaxite");
        $form->addButton("Lifeboat\nplay.lbsg.net:19132", 0, "textures/other/lifeboat", "lifeboat");
        $form->addButton("Mineplex\npe.mineplex.com:19132", 0, "textures/other/mineplex", "mineplex");
        $form->addButton("Minevile\nplay.inpvp.net:19132", 0, "textures/other/minevile", "minevile");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openNewServer(Player $player, string $label = null)
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data === null) {
                $this->openMainMenu($player);
                return;
            }
            if (is_numeric($data["port"])) {
                $array = ["name" => $data["name"], "ip" => $data["ip"], "port" => $data["port"]];
                $config = new Config($this->getDataFolder() . $player->getName() . ".yml", Config::YAML);
                $config->set($data["name"], $array);
                $config->save();
                $this->openMainMenu($player);
            } else {
                $this->openNewServer($player, "The port " . $data["port"] . " is invalid");
            }
            return;
        });
        $form->setTitle("New server");
        if ($label !== null) $form->addLabel($label);
        $form->addInput("Name", "Name (Required)", null, "name");
        $form->addInput("IP", "IP (required)", null, "ip");
        $form->addInput("Port", "Port (required)", "19132", "port");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openRemoveServer(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                $this->openMainMenu($player);
                return;
            }
            $config = new Config($this->getDataFolder() . $player->getName() . ".yml", Config::YAML);
            $config->set($data["name"], null);
            $config->save();
            $this->openMainMenu($player);
            return;
        });
        $form->setTitle("Remove a server");
        $config = new Config($this->getDataFolder() . $player->getName() . ".yml", Config::YAML);
        $form->setContent("Pick a server to remove");
        foreach ($config->getAll() as $value) {
            if ($value !== null) $form->addButton($value["name"] . "\n" . $value["ip"] . ":" . $value["port"], 0, "textures/ui/servers", $value);
        }
        $form->sendToPlayer($player);
        return $form;
    }
}