<?php
// Timer Author   | CØDE#4384
// Contributors   | Robo_N1X#1821 / VitaminTHC#3609
// DiscordPHP     | https://github.com/discord-php

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use Discord\DiscordCommandClient;
// use Discord\Slash\Client;
use Discord\Builders\MessageBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\Components\SelectMenu;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\Activity;
use Discord\Parts\Channel\Channel;

// $discord->on("ready", function () {
//     $channel = $discord->factory(Channel::class);
//     $channel->type = Channel::TYPE_CATEGORY;
//     $guild = $discord->guilds->first();
//     $guild->channels->save($channel)->done();
// });

$discord = new DiscordCommandClient([
  'token' => 'PLACE_TOKEN_HERE', // Put your Bot token here from https://discord.com/developers/applications/
]);

$TT_timers = [];
$DT_timers = [];
$TT_EndMsg = '🔥 !Toke Timer Completed! 🔥';
$DT_EndMsg = '💨 !Dab Timer Completed! 💨';

$discord->on('ready', function ($discord) {
  $channel = $discord->factory(Channel::class);
  $channel->type = Channel::TYPE_CATEGORY;
  // $guild = $discord->guilds->first();
  $discord->guilds->first()->channels->save($channel)->done();
  echo "
    >>>>>>>>>>>>>>> TimmyTimer Bot is Ready <<<<<<<<<<<<<<<
  ", PHP_EOL;

  $activity = $discord->factory(Activity::class, [
    'name' => '(!t #) (!d # #)',
    'type' => Activity::TYPE_PLAYING
  ]);
  $discord->updatePresence($activity);
  // Listen for events here
  $discord->on('message', function ($message) {

    echo "> NEW MESSAGE: {$message->author->username}({$message->author->id}): {$message->content}", PHP_EOL;

    $TT_checker = substr($message->content, 0, 3);
    if($message->content == '!t' || $TT_checker == '!t ') {
      $message->delete();
      global $discord, $TT_timers;
      $TT_user = $message->member->id;
      $TT_channel = $message->channel_id;
      $TT_arg = explode(' ', $message->content);
      $TT_delay = $TT_arg[1];
      // check format
      if (!ctype_digit($TT_delay)) {
        TT__IncorrectFormat($discord, $message, $TT_delay);
        return;
      }
      if ($TT_delay < 10) {
        TT__IncorrectFormat($discord, $message, $TT_delay);
        return;
      }

      // check started status
      if (!array_key_exists($TT_channel, $TT_timers)) {
        TT__Starter($discord, $message, $TT_channel, $TT_user, $TT_delay);
      } else {
        if (!in_array($TT_user, $TT_timers[$TT_channel]['users'])) {
          TT__AddUser($discord, $message, $TT_channel, $TT_user);
        } else {
          TT__UserAlreadyExist($discord, $message, $TT_channel, $TT_user);
        }
      }
    }

    $DT_checker = substr($message->content, 0, 3);
    if($message->content == '!d' || $DT_checker == '!d ') {
      $message->delete();
      global $discord, $DT_timers;
      $DT_user = $message->member->id;
      $DT_channel = $message->channel_id;
      $DT_arg = explode(' ', $message->content);
      $DT_delay = $DT_arg[1];
      $DT_cooldown = $DT_arg[2];
      // check format
      if (!ctype_digit($DT_delay)) {
        DT__IncorrectFormat($discord, $message, $DT_delay);
        return;
      }
      if (!ctype_digit($DT_cooldown)) {
        DT__IncorrectFormat($discord, $message, $DT_cooldown);
        return;
      }
      if ($DT_delay < 5) {
        DT__IncorrectFormat($discord, $message, $DT_delay);
        return;
      }
      if ($DT_cooldown < 5) {
        DT__IncorrectFormat($discord, $message, $DT_cooldown);
        return;
      }

      // check started status
      if (!array_key_exists($DT_channel, $DT_timers)) {
        DT__Starter($discord, $message, $DT_channel, $DT_user, $DT_delay, $DT_cooldown);
      } else {
        if (!in_array($DT_user, $DT_timers[$DT_channel]['users'])) {
          DT__AddUser($discord, $message, $DT_channel, $DT_user);
        } else {
          DT__UserAlreadyExist($discord, $message, $DT_channel, $DT_user);
        }
      }
    }

    // if ($message->content == '!remindwoosh'){
    //   $message->delete();
    //   global $discord;
    //   $embed = [
    //     'title' => ':bangbang: WOOSH  UPDATE VALORANT :bangbang:',
    //     'description' => '<@139863921669570560> Update Valorant Bitch!'
    //   ];
    //   $message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed));
    //   remindWoosh($discord, $message);
    // }

    if ($message->content == '!towner'){
      global $discord, $TT_timers;
      $TT_user = $message->member->id;
      $TT_channel = $message->channel_id;
      // check started status
      if (!array_key_exists($TT_channel, $TT_timers)) {
        $embed = [
          'title' => 'No Timer Started!',
          'description' => 'Start a timer by typing !t ##'
        ];
      } else {
        $embed = [
          'title' => 'Timer Owner',
          'description' => '🛡️ <@'.$TT_timers[$TT_channel]['users'][0].'>'
        ];
      }
      $builder = MessageBuilder::new();
      $builder->addEmbed($embed);
      $message->channel->sendMessage($builder);
    }
    if ($message->content == '!tclearusers'){
      global $discord, $TT_timers;
      $TT_timers[$TT_channel]['users'][] = '';
    }
    if ($message->content == '!h'){
      $embed = [
        'title' => '❓ ' . $timer_delay . ' TimmyTimer Command ListInfo',
        'description' => '
          `!t #`
          `!towner`
          `!tclearusers`
          `!d # #`'
      ];
      $builder = MessageBuilder::new();
      $builder->addEmbed($embed);
      $message->channel->sendMessage($builder);
    }
    if ($message->content == '!tstop') {
      global $discord, $TT_timers;
      $TT_channel = $message->channel_id;
      $discord->getLoop()->cancelTimer($GLOBALS[$TT_channel . '_Reminder']);
      $discord->getLoop()->cancelTimer($GLOBALS[$TT_channel]);
      unset($TT_timers[$TT_channel]);
      $embed = [
        'title' => 'TIMER STOPPED!'
      ];
      $message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed), false);
      $discord->getLoop()->addTimer(5, function () use ($message) {
        return $message->delete(); //Delete message after 10 seconds
      });
    }
    //this will close the bot
    if ($message->content == '!exit'){
    	$discord->close();
    }

    if (preg_match('/ping/', $message->content)){
    	$message->reply("pong");
    }

    // log only commands requests
    if ($message->content == '!t' || $TT_checker == '!t ' || $message->content == '!h' || $message->content == '!tclearusers' || $message->content == '!towner') {
      date_default_timezone_set('America/Chicago');
      $current_date = date('m.d.Y G:i:s');
      $file = 'chat-log.html';
      $current = file_get_contents($file);
      $current .= $current_date. ' ' .$message->author->username.': '.$message->content.PHP_EOL;
      file_put_contents($file, $current);
    }
    // Logs that the bot responded / doesnt log actually response
    if ($message->author->username == "TimmyTimer"){
      date_default_timezone_set('America/Chicago');
      $current_date = date('m.d.Y G:i:s');
      $file = 'chat-log.html';
      $current = file_get_contents($file);
      $current .= $current_date. ' ' .$message->author->username.': RESPONDED' . PHP_EOL;
      file_put_contents($file, $current);
    }
  });//end small function with content
});//end main function ready

function remindLooper($discord, $message) {
  $_GLOBALS['remindloop'] = $discord->getLoop()->addPeriodicTimer('3600', function() use ($message) {
    $embed = [
      'title' => ':bangbang: remind Looper :bangbang:',
      'description' => '<@USERIDTOTAG> remind Looper!'
    ];
    $message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed));
  });
}


// ----------------------------------------------------------------------------- TIMERS

function TT__IncorrectFormat($discord, $message, $TT_delay) {
  echo 'touched';
  $embed = [
    'title' => '❌ Incorrect Format: ' . $TT_delay,
    'description' => '**Format Required:**
    !t [seconds >= 10]'
  ];
  $message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed), false);
}

function TT__Starter($discord, $message, $TT_channel, $TT_user, $TT_delay) {
  global $TT_timers;
  $TT_timers[] = $TT_channel;
  $TT_timers[$TT_channel]['users'][] = $TT_user;
  $TT_timers[$TT_channel]['delay'][] = $TT_delay;
  $mention_users = NULL;
  foreach($TT_timers[$TT_channel]['users'] as $current_TT_user) {
    $mention_users .= '<@' . $current_TT_user . '> ';
  }
  $embed = [
    'title' => '⏱️ ' . $TT_delay . ' Second Timer Started!',
    'description' => $mention_users
  ];
  $builder = MessageBuilder::new();
  $row = ActionRow::new();
  $button = TT__JoinButton($message, $TT_channel, $TT_delay);
  $row->addComponent($button);
  // $button = TT__Extend30Button($message, $TT_channel, $TT_delay);
  // $row->addComponent($button);
  // $button = TT__Extend60Button($message, $TT_channel, $TT_delay);
  // $row->addComponent($button);
  $button = TT__StopButton($discord, $message, $TT_channel, $TT_delay);
  $row->addComponent($button);
  $builder->addComponent($row);
  $builder->addEmbed($embed);
  // $message->channel->sendMessage($builder)->done(function ($builder) use ($TT_channel) {
  //   $builder->delayedDelete(10000);
  // });
  $message->channel->sendMessage($builder)->done(function ($builder) use ($TT_channel, $TT_delay) {
    // global $TT_timers;
    // $TT_timers[$TT_channel]['embed_message_id'][] = $builder->id;
    $TT_Complete = $TT_delay * 1000;
    $builder->delayedDelete($TT_Complete);
  });

  // $discord->getLoop()->addTimer(1, function() use ($discord, $message, $TT_channel) {
  //   TT__ReduceTimer($discord, $message, $TT_channel);
  // });

  $TT_mid_delay = ($TT_delay/2);
  $GLOBALS[$TT_channel . '_Reminder'] = $discord->getLoop()->addTimer($TT_mid_delay, function() use ($discord, $message, $TT_channel) {
    TT__MidReminder($discord, $message, $TT_channel);
  });
  $GLOBALS[$TT_channel] = $discord->getLoop()->addTimer($TT_delay, function() use ($discord, $message, $TT_channel) {
    TT__Complete($discord, $message, $TT_channel);
  });
}

function TT__MidReminder($discord, $message, $TT_channel) {
  global $TT_timers;
  $mention_users = NULL;
  foreach($TT_timers[$TT_channel]['users'] as $current_TT_user) {
    $mention_users .= '<@' . $current_TT_user . '> ';
  }
  foreach($TT_timers[$TT_channel]['delay'] as $current_TT_delay) {
    $mention_delay = $current_TT_delay;
  }
  $mid_TT_delay = ($current_TT_delay/2);
  $embed = [
    'title' => '⏱️ ' . $mid_TT_delay . ' Seconds Remaining!',
    'description' => '👥 ' . $mention_users
  ];
  $builder = MessageBuilder::new();
  $row = ActionRow::new();
  $builder->addEmbed($embed);
  $message->channel->sendMessage($builder)->done(function ($builder) use ($TT_channel, $mid_TT_delay) {
    $builder->delayedDelete(($mid_TT_delay*1000));
  });
  // return $embed;
}

function TT__JoinButton($message, $TT_channel, $TT_delay) {
  global $discord, $message, $TT_timers;
  $button = Button::new(Button::STYLE_SUCCESS);
  $button->setLabel('Join Timer');
  $button->setListener(function (Interaction $interaction) {
    $TT_channel = $interaction->channel_id;
    global $discord, $message, $TT_timers;
    $TT_user = str_replace(['<', '@', '>'], '', $interaction->user);
    $mention_users = null;
    if (array_key_exists($TT_channel, $TT_timers)) {
      if (!in_array($TT_user, $TT_timers[$TT_channel]['users'])) {
        foreach($TT_timers[$TT_channel]['delay'] as $current_TT_delay) {
          $TT_delay = $current_TT_delay;
        }
        $embed = TT__AddUser_Interaction($TT_channel, $TT_user, $TT_delay);
        $builder = MessageBuilder::new();
        $row = ActionRow::new();
        $button = TT__JoinButton($message, $TT_channel, $TT_delay);
        $row->addComponent($button);
        // $button = TT__Extend30Button($message, $TT_channel, $TT_delay);
        // $row->addComponent($button);
        // $button = TT__Extend60Button($message, $TT_channel, $TT_delay);
        // $row->addComponent($button);
        $button = TT__StopButton($discord, $message, $TT_channel, $TT_delay);
        $row->addComponent($button);
        $builder->addComponent($row);
        $builder->addEmbed($embed);
        $interaction->updateMessage($builder);
      } else {
        $embed = TT__UserAlreadyExist($TT_channel, $TT_user);
        $builder = MessageBuilder::new();
        $builder->addEmbed($embed);
        $interaction->respondWithMessage($builder, true);
      }
    }
  }, $discord);
  return $button;
}

function TT__StopButton($discord, $message, $TT_channel, $TT_delay) {
  global $discord, $message, $TT_timers;
  $button = Button::new(Button::STYLE_DANGER);
  $button->setLabel('Stop Timer');
  $button->setListener(function (Interaction $interaction) {
    $TT_channel = $interaction->channel_id;
    global $discord, $message, $TT_timers;
    $TT_user = str_replace(['<', '@', '>'], '', $interaction->user);
    if (array_key_exists($TT_channel, $TT_timers)) {
      if($DT_user == $DT_timers[$DT_channel]['users'][0]) {
        $discord->getLoop()->cancelTimer($GLOBALS[$TT_channel . '_Reminder']);
        $discord->getLoop()->cancelTimer($GLOBALS[$TT_channel]);
        unset($TT_timers[$TT_channel]);
        $embed = [
          'title' => 'TIMER STOPPED!'
        ];
        $builder = MessageBuilder::new();
        $row = ActionRow::new();
        $builder->addEmbed($embed);
        $interaction->updateMessage($builder);
        $discord->getLoop()->addTimer(5, function () use ($interaction) {
          return $interaction->message->delete();
        });
      } else {
        $embed = [
          'title' => 'You Dont Own This Timer!'
        ];
        $builder = MessageBuilder::new();
        $row = ActionRow::new();
        $builder->addEmbed($embed);
        $interaction->respondWithMessage($builder, true);
      }
    } else {
      $embed = [
        'title' => 'TIMER STOPPED!'
      ];
      $builder = MessageBuilder::new();
      $row = ActionRow::new();
      $builder->addEmbed($embed);
      $interaction->updateMessage($builder);
      $discord->getLoop()->addTimer(5, function () use ($interaction) {
        return $interaction->message->delete();
      });
    }
  }, $discord);
  return $button;
}

function TT__Extend30Button($message, $TT_channel, $TT_delay) {
  global $discord, $message, $TT_timers;
  $button = Button::new(Button::STYLE_SECONDARY);
  $button->setLabel('+ 30secs');
  $button->setListener(function (Interaction $interaction) {
    $embed = [
      'description' => 'Extending Timer 30 Seconds...'
    ];
    $builder = MessageBuilder::new();
    $builder->addEmbed($embed);
    $interaction->respondWithMessage($builder, true);
  }, $discord);
  return $button;
}

function TT__Extend60Button($message, $TT_channel, $TT_delay) {
  global $discord, $message, $TT_timers;
  $button = Button::new(Button::STYLE_SECONDARY);
  $button->setLabel('+ 60secs');
  $button->setListener(function (Interaction $interaction) {
    $embed = [
      'description' => 'Extending Timer 60 Seconds...'
    ];
    $builder = MessageBuilder::new();
    $builder->addEmbed($embed);
    $interaction->respondWithMessage($builder, true);
  }, $discord);
  return $button;
}

function TT__AddUser($discord, $message, $TT_channel, $user) {
  global $TT_timers;
  $TT_user = $user;
  $TT_timers[$TT_channel]['users'][] = $TT_user;
  $embed = [
    'description' => '👥 <@' . $TT_user . '> added to the timer!'
  ];
  $message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed), false);
}

function TT__AddUser_Interaction($TT_channel, $user, $TT_delay) {
  $mention_users = NULL;
  global $TT_timers;
  $TT_user = $user;
  // $TT_timers[$TT_channel]['delay'][] = $TT_delay;
  $TT_timers[$TT_channel]['users'][] = $TT_user;
  foreach($TT_timers[$TT_channel]['users'] as $current_TT_user) {
    $mention_users .= '<@' . $current_TT_user . '> ';
  }
  foreach($TT_timers[$TT_channel]['delay'] as $current_TT_delay) {
    $mention_delay = $current_TT_delay;
  }
  $embed = [
    'title' => '⏱️ ' . $current_TT_delay . ' Second Timer Started!',
    'description' => '👥 ' . $mention_users
  ];
  return $embed;
}

function TT__UserAlreadyExist($TT_channel, $user) {
  $embed = [
    'description' => "❌ <@" . $user . ">, You've already joined the timer! Have Patience!"
  ];
  return $embed;
}

function TT__Complete($discord, $message, $TT_channel) {
  global $TT_timers, $TT_EndMsg;
  $mention_users = NULL;
  foreach($TT_timers[$TT_channel]['users'] as $current_TT_user) {
    $mention_users .= '<@' . $current_TT_user . '> ';
  }
  $embed = [
    'title' => $TT_EndMsg,
    'description' => '👥 ' . $mention_users
  ];
  $builder = MessageBuilder::new();
  $row = ActionRow::new();
  $builder->addEmbed($embed);
  $message->channel->sendMessage($builder)->done(function ($builder) use ($TT_channel) {
    $builder->delayedDelete(10000);
  });
  // reset
  unset($TT_timers[$TT_channel]);
}


// ----------------------------------------------------------------------------- DABS

function DT__IncorrectFormat($discord, $message, $DT_delay) {
  echo 'touched';
  $embed = [
    'title' => '❌ Incorrect Dab Format: ' . $DT_delay,
    'description' => '**Format Required:**
    !d [seconds >= 10] [seconds >= 10]'
  ];
  $message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed), false);
}

function DT__Starter($discord, $message, $DT_channel, $DT_user, $DT_delay, $DT_cooldown) {
  global $DT_timers;
  $DT_timers[] = $DT_channel;
  // $DT_timers[$DT_channel]['users'][] = 'person';
  $DT_timers[$DT_channel]['users'][] = $DT_user;
  $DT_timers[$DT_channel]['delay'][] = $DT_delay;
  $DT_timers[$DT_channel]['cooldown'][] = $DT_cooldown;
  $mention_users = NULL;
  foreach($DT_timers[$DT_channel]['users'] as $current_DT_user) {
    $mention_users .= '<@' . $current_DT_user . '> ';
  }
  $embed = [
    'title' => '⏱️ Dab Timer: 🔥 ' . $DT_delay . ' Second Heating | ❄️ ' . $DT_cooldown . ' Second Cooldown!',
    'description' => $mention_users
    // 'color' => '0x00ff00'
  ];
  $builder = MessageBuilder::new();
  $row = ActionRow::new();
  $button = DT__JoinButton($message, $DT_channel, $DT_delay);
  $row->addComponent($button);
  $button = DT__StopButton($discord, $message, $DT_channel, $DT_delay);
  $row->addComponent($button);
  $builder->addComponent($row);
  $builder->addEmbed($embed);
  // $message->channel->sendMessage($builder);
  $message->channel->sendMessage($builder)->done(function ($builder) use ($DT_channel, $DT_delay) {
    $DT_Complete = $DT_delay * 1000;
    $builder->delayedDelete($DT_Complete);
  });


  $DT_mid_delay = ($DT_delay/2);
  $GLOBALS[$DT_channel . '_Reminder'] = $discord->getLoop()->addTimer($DT_mid_delay, function() use ($discord, $message, $DT_channel) {
    DT__MidReminder($discord, $message, $DT_channel);
  });
  $GLOBALS[$DT_channel . '_Cooldown'] = $discord->getLoop()->addTimer($DT_delay, function() use ($discord, $message, $DT_channel) {
    DT__CooldownStart($discord, $message, $DT_channel);
  });
  $GLOBALS[$DT_channel] = $discord->getLoop()->addTimer(($DT_delay+$DT_cooldown), function() use ($discord, $message, $DT_channel) {
    DT__Complete($discord, $message, $DT_channel);
  });
}

function DT__MidReminder($discord, $message, $DT_channel) {
  global $DT_timers;
  $mention_users = NULL;
  foreach($DT_timers[$DT_channel]['users'] as $current_DT_user) {
    $mention_users .= '<@' . $current_DT_user . '> ';
  }
  foreach($DT_timers[$DT_channel]['delay'] as $current_DT_delay) {
    $mention_delay = $current_DT_delay;
  }
  $mid_DT_delay = ($current_DT_delay/2);
  $embed = [
    'title' => '⏱️ ' . $mid_DT_delay . ' Seconds Remaining!',
    'description' => '👥 ' . $mention_users
  ];
  $builder = MessageBuilder::new();
  $row = ActionRow::new();
  $builder->addEmbed($embed);
  $message->channel->sendMessage($builder)->done(function ($builder) use ($DT_channel, $mid_DT_delay) {
    $builder->delayedDelete(($mid_DT_delay*1000));
  });
  // return $embed;
}

function DT__JoinButton($message, $DT_channel, $DT_delay) {
  global $discord, $message, $DT_timers;
  $button = Button::new(Button::STYLE_SUCCESS);
  $button->setLabel('Join Dab');
  $button->setListener(function (Interaction $interaction) {
    $DT_channel = $interaction->channel_id;
    global $discord, $message, $DT_timers;
    $DT_user = str_replace(['<', '@', '>'], '', $interaction->user);
    $mention_users = null;
    if (array_key_exists($DT_channel, $DT_timers)) {
      if (!in_array($DT_user, $DT_timers[$DT_channel]['users'])) {
        foreach($DT_timers[$DT_channel]['delay'] as $current_DT_delay) {
          $DT_delay = $current_DT_delay;
        }
        $embed = DT__AddUser_Interaction($DT_channel, $DT_user);
        $builder = MessageBuilder::new();
        $row = ActionRow::new();
        $button = DT__JoinButton($message, $DT_channel, $DT_delay);
        $row->addComponent($button);
        // $button = DT__Extend30Button($message, $DT_channel, $DT_delay);
        // $row->addComponent($button);
        // $button = DT__Extend60Button($message, $DT_channel, $DT_delay);
        // $row->addComponent($button);
        $button = DT__StopButton($discord, $message, $DT_channel, $DT_delay);
        $row->addComponent($button);
        $builder->addComponent($row);
        $builder->addEmbed($embed);
        $interaction->updateMessage($builder);
      } else {
        $embed = DT__UserAlreadyExist($DT_channel, $DT_user);
        $builder = MessageBuilder::new();
        $builder->addEmbed($embed);
        $interaction->respondWithMessage($builder, true);
      }
    }
  }, $discord);
  return $button;
}

function DT__StopButton($discord, $message, $DT_channel, $DT_delay) {
  global $discord, $message, $DT_timers;
  $button = Button::new(Button::STYLE_DANGER);
  $button->setLabel('Stop Dab');
  $button->setListener(function (Interaction $interaction) {
    $DT_channel = $interaction->channel_id;
    global $discord, $message, $DT_timers;
    $DT_user = str_replace(['<', '@', '>'], '', $interaction->user);
    if (array_key_exists($DT_channel, $DT_timers)) {
      if($DT_user == $DT_timers[$DT_channel]['users'][0]) {
        $discord->getLoop()->cancelTimer($GLOBALS[$DT_channel . '_Reminder']);
        $discord->getLoop()->cancelTimer($GLOBALS[$DT_channel . '_Cooldown']);
        $discord->getLoop()->cancelTimer($GLOBALS[$DT_channel]);
        unset($DT_timers[$DT_channel]);
        $embed = [
          'title' => 'TIMER STOPPED!'
        ];
        $builder = MessageBuilder::new();
        $row = ActionRow::new();
        $builder->addEmbed($embed);
        $interaction->updateMessage($builder);
        $discord->getLoop()->addTimer(5, function () use ($interaction) {
          return $interaction->message->delete(); //Delete message after 10 seconds
        });
      } else {
        $embed = [
          'title' => 'You Dont Own This Timer!'
        ];
        $builder = MessageBuilder::new();
        $row = ActionRow::new();
        $builder->addEmbed($embed);
        $interaction->respondWithMessage($builder, true);
      }
    } else {
      $embed = [
        'title' => 'TIMER STOPPED!'
      ];
      $builder = MessageBuilder::new();
      $row = ActionRow::new();
      $builder->addEmbed($embed);
      $interaction->updateMessage($builder);
      $discord->getLoop()->addTimer(5, function () use ($interaction) {
        return $interaction->message->delete(); //Delete message after 10 seconds
      });
    }
  }, $discord);
  return $button;
}

function DT__Extend30Button($message, $DT_channel, $DT_delay) {
  global $discord, $message, $DT_timers;
  $button = Button::new(Button::STYLE_SECONDARY);
  $button->setLabel('+ 30secs');
  $button->setListener(function (Interaction $interaction) {
    $embed = [
      'description' => 'Extending Timer 30 Seconds...'
    ];
    $builder = MessageBuilder::new();
    $builder->addEmbed($embed);
    $interaction->respondWithMessage($builder, true);
  }, $discord);
  return $button;
}

function DT__Extend60Button($message, $DT_channel, $DT_delay) {
  global $discord, $message, $DT_timers;
  $button = Button::new(Button::STYLE_SECONDARY);
  $button->setLabel('+ 60secs');
  $button->setListener(function (Interaction $interaction) {
    $embed = [
      'description' => 'Extending Timer 60 Seconds...'
    ];
    $builder = MessageBuilder::new();
    $builder->addEmbed($embed);
    $interaction->respondWithMessage($builder, true);
  }, $discord);
  return $button;
}

function DT__AddUser($discord, $message, $DT_channel, $user) {
  global $DT_timers;
  $DT_user = $user;
  $DT_timers[$DT_channel]['users'][] = $DT_user;
  $embed = [
    'description' => '👥 <@' . $DT_user . '> added to the timer!'
  ];
  $message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed), false);
}

function DT__AddUser_Interaction($DT_channel, $user) {
  $mention_users = NULL;
  global $DT_timers;
  $DT_user = $user;
  $DT_timers[$DT_channel]['users'][] = $DT_user;
  foreach($DT_timers[$DT_channel]['users'] as $current_DT_user) {
    $mention_users .= '<@' . $current_DT_user . '> ';
  }
  foreach($DT_timers[$DT_channel]['delay'] as $current_DT_delay) {
    $mention_delay = $current_DT_delay;
  }
  foreach($DT_timers[$DT_channel]['cooldown'] as $current_DT_cooldown) {
    $mention_cooldown = $current_DT_cooldown;
  }
  $embed = [
    'title' => '⏱️ Dab Timer: 🔥 ' . $current_DT_delay . ' Second Heating | ❄️ ' . $current_DT_cooldown . ' Second Cooldown!',
    'description' => '👥 ' . $mention_users
  ];
  return $embed;
}

function DT__UserAlreadyExist($DT_channel, $user) {
  $embed = [
    'description' => "❌ <@" . $user . ">, You've already joined the timer! Have Patience!"
  ];
  return $embed;
}

function DT__Complete($discord, $message, $DT_channel) {
  global $DT_timers, $DT_EndMsg;
  $mention_users = NULL;
  foreach($DT_timers[$DT_channel]['users'] as $current_DT_user) {
    $mention_users .= '<@' . $current_DT_user . '> ';
  }
  $embed = [
    'title' => $DT_EndMsg,
    'description' => '👥 ' . $mention_users
  ];
  $builder = MessageBuilder::new();
  $row = ActionRow::new();
  $builder->addEmbed($embed);
  $message->channel->sendMessage($builder)->done(function ($builder) {
    $builder->delayedDelete(10000);
  });
  // reset
  unset($DT_timers[$DT_channel]);
}

function DT__CooldownStart($discord, $message, $DT_channel) {
  global $DT_timers;
  $mention_users = NULL;
  foreach($DT_timers[$DT_channel]['users'] as $current_DT_user) {
    $mention_users .= '<@' . $current_DT_user . '> ';
  }
  foreach($DT_timers[$DT_channel]['cooldown'] as $current_DT_cooldown) {
    $mention_cooldown = $current_DT_cooldown;
  }
  $embed = [
    'title' => '❄️ !! STOP HEATING -> ' .$mention_cooldown. ' SECOND COOLDOWN !!  ❄️',
    'description' => '👥 ' . $mention_users
  ];
  $builder = MessageBuilder::new();
  $row = ActionRow::new();
  $builder->addEmbed($embed);
  // $message->channel->sendMessage($builder);
  $message->channel->sendMessage($builder)->done(function ($builder) use ($DT_channel, $mention_cooldown) {
    $builder->delayedDelete(($mention_cooldown*1000));
  });
}

// function TT__ReduceTimer($discord, $message, $TT_channel) {
//   global $TT_timers;
//   foreach($TT_timers[$TT_channel]['delay'] as $current_TT_delay) {
//     $active_TT_delay = $current_TT_delay;
//   }
//   unset($TT_timers[$TT_channel]['delay']);
//   $new_TT_delay = ($active_TT_delay-1);
//   $TT_timers[$TT_channel]['delay'][] = $new_TT_delay;
//
//   echo "
//   > NEW DELAY: $new_TT_delay
//   ", PHP_EOL;
//   $discord->getLoop()->addTimer(1, function() use ($discord, $message, $TT_channel) {
//     TT__ReduceTimer($discord, $message, $TT_channel);
//   });
// }

$discord->run();
?>
