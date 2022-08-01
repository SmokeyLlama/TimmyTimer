<?php

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use Discord\DiscordCommandClient;
use Discord\Slash\RegisterClient;

$client = new RegisterClient(''); // Put your Bot token here from https://discord.com/developers/applications/
// $guildCommands = $client->getCommands('guild_id');

// creates a global command
// $command = $client->createGlobalCommand('t', 'Start a group timer!', [
//     // optional array of options
// ]);
// $command = $client->createGlobalCommand('timer', 'Start a group timer!', [
//     // optional array of options
// ]);
// $client->deleteCommand($command);
// $command = $client->createGlobalCommand('t', 'Start a group timer!', [
//     // optional array of options
// ]);
// $client->deleteCommand($command);


?>
