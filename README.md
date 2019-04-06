# Rainbow Six Siege Player Stats API
This API queries the Rainbow Six Siege stats from any given player by name or uplayID. Multiple and mixed requests at one are also possible.

## Installation:
1. Clone this whole repo (git clone).
2. Upload it to your webserver to any location.
3. In the config.php change the **EMAIL** and the **PASSWORD** fields to an uplay account of your choice.
4. For your security you will need to set an appcode in config.php file and append GET parameter *appcode* to all request (WARNING: Obviously you should not use spaces).
5. Give the webserver permissions to edit the *api_ticket* file, usually 660 or 777 (this one ONLY for tests).
6. Finished!

## Usage:
### getUser.php:
With this API you can query multiple users at one and it does not matter if you use their names, uplayID's or both (GET requests). You can also specify platform (one for all players) and progression if you want to see extra info like level or xp. Obviously for uplayID you shouldn't specify platform.

Here are some examples:
```
https://website.com/api/r6/getUser.php?id=735e4640-32d3-484f-ba56-f80030d35337&appcode=test
https://website.com/api/r6/getUser.php?name=AE_SeemsLegit&appcode=test
https://website.com/api/r6/getUser.php?id=735e4640-32d3-484f-ba56-f80030d35337&name=AE_SeemsLegit&appcode=test
https://website.com/api/r6/getUser.php?name=AE_SeemsLegit&platform=uplay&appcode=test
https://website.com/api/r6/getUser.php?id=735e4640-32d3-484f-ba56-f80030d35337&progression=true&platform=uplay&appcode=test
```
As you can see it does not matter if you give the api a name or uplay id. It's just important that you never forget to put the **appcode** into you request.

### Optional Arguments:
```
&season=SEASON (Default -1)
&region=REGION (Default emea)
&platform=PLATFORM (Default uplay)
&progression=PROGRESSION (Default true)
```

Here are the example responses from the GET requests mentioned above:
```json
{
   "players":{
      "735e4640-32d3-484f-ba56-f80030d35337":{
         "board_id":"pvp_ranked",
         "past_seasons_abandons":0,
         "update_time":"2018-04-08T22:47:21.650000+00:00",
         "skill_mean":17.7856229582,
         "abandons":2,
         "season":9,
         "region":"emea",
         "profile_id":"735e4640-32d3-484f-ba56-f80030d35337",
         "past_seasons_losses":10,
         "max_mmr":2621.04601985,
         "mmr":1778.56229582,
         "wins":77,
         "skill_stdev":3.9493432727,
         "rank":5,
         "losses":78,
         "next_rank_mmr":1800,
         "past_seasons_wins":10,
         "previous_rank_mmr":1700,
         "max_rank":8,
         "nickname":"AE_BadKey",
         "platform":"uplay"
      }
   }
}
```
```json
{
   "players":{
      "a39c7ad5-3282-467c-bc85-f65b0e61cde4":{
         "board_id":"pvp_ranked",
         "past_seasons_abandons":0,
         "update_time":"2018-04-08T16:05:13.150000+00:00",
         "skill_mean":26.2664770288,
         "abandons":0,
         "season":9,
         "region":"emea",
         "profile_id":"a39c7ad5-3282-467c-bc85-f65b0e61cde4",
         "past_seasons_losses":19,
         "max_mmr":2839.11421816,
         "mmr":2626.64770288,
         "wins":34,
         "skill_stdev":5.28927628982,
         "rank":13,
         "losses":31,
         "next_rank_mmr":2700,
         "past_seasons_wins":8,
         "previous_rank_mmr":2500,
         "max_rank":14,
         "nickname":"AE_SeemsLegit",
         "platform":"uplay"
      }
   }
}
```
```json
{
   "players":{
      "735e4640-32d3-484f-ba56-f80030d35337":{
         "board_id":"pvp_ranked",
         "past_seasons_abandons":0,
         "update_time":"2018-04-08T22:47:21.650000+00:00",
         "skill_mean":17.7856229582,
         "abandons":2,
         "season":9,
         "region":"emea",
         "profile_id":"735e4640-32d3-484f-ba56-f80030d35337",
         "past_seasons_losses":10,
         "max_mmr":2621.04601985,
         "mmr":1778.56229582,
         "wins":77,
         "skill_stdev":3.9493432727,
         "rank":5,
         "losses":78,
         "next_rank_mmr":1800,
         "past_seasons_wins":10,
         "previous_rank_mmr":1700,
         "max_rank":8,
         "nickname":"AE_BadKey",
         "platform":"uplay"
      },
      "a39c7ad5-3282-467c-bc85-f65b0e61cde4":{
         "board_id":"pvp_ranked",
         "past_seasons_abandons":0,
         "update_time":"2018-04-08T16:05:13.150000+00:00",
         "skill_mean":26.2664770288,
         "abandons":0,
         "season":9,
         "region":"emea",
         "profile_id":"a39c7ad5-3282-467c-bc85-f65b0e61cde4",
         "past_seasons_losses":19,
         "max_mmr":2839.11421816,
         "mmr":2626.64770288,
         "wins":34,
         "skill_stdev":5.28927628982,
         "rank":13,
         "losses":31,
         "next_rank_mmr":2700,
         "past_seasons_wins":8,
         "previous_rank_mmr":2500,
         "max_rank":14,
         "nickname":"AE_SeemsLegit",
         "platform":"uplay"
      }
   }
}
```
```json
{
   "players":{
      "a39c7ad5-3282-467c-bc85-f65b0e61cde4":{
         "board_id":"pvp_ranked",
         "past_seasons_abandons":0,
         "update_time":"2018-06-26T06:34:00.118000+00:00",
         "skill_mean":26.2304694258,
         "abandons":0,
         "season":10,
         "region":"emea",
         "profile_id":"a39c7ad5-3282-467c-bc85-f65b0e61cde4",
         "past_seasons_losses":50,
         "max_mmr":2739.38864066,
         "mmr":2623.04694258,
         "wins":2,
         "skill_stdev":8.08017479399,
         "rank":0,
         "losses":1,
         "next_rank_mmr":0,
         "past_seasons_wins":42,
         "previous_rank_mmr":0,
         "max_rank":0,
         "nickname":"AE_SeemsLegit",
         "platform":"uplay"
      }
   }
}
```
```json
{
   "players":{
      "735e4640-32d3-484f-ba56-f80030d35337":{
         "xp":3915,
         "profile_id":"735e4640-32d3-484f-ba56-f80030d35337",
         "lootbox_probability":1990,
         "level":93,
         "board_id":"pvp_ranked",
         "past_seasons_abandons":2,
         "update_time":"2018-06-26T06:34:13.202000+00:00",
         "skill_mean":26.3109675568,
         "abandons":0,
         "season":10,
         "region":"emea",
         "past_seasons_losses":88,
         "max_mmr":2861.87755106,
         "mmr":2631.09675568,
         "wins":3,
         "skill_stdev":7.91688219112,
         "rank":0,
         "losses":2,
         "next_rank_mmr":0,
         "past_seasons_wins":87,
         "previous_rank_mmr":0,
         "max_rank":0,
         "nickname":"AE_BadKey",
         "platform":"uplay"
      }
   }
}
```
### getSmallUser.php:
This function only returns the uplayID, the uplay name of any given player(s) (by uid or name) and the relative platforms.

### Optional Arguments:
```
&platform=PLATFORM (Default uplay)
```

Examples:
```
https://website.com/api/r6/getSmallUser.php?name=Sidelux00,Sir.Avocado&appcode=test
https://website.com/api/r6/getSmallUser.php?name=Sidelux00&appcode=test
https://website.com/api/r6/getSmallUser.php?id=40078dc7-5f24-49a6-ad27-070c9c528f6c&appcode=test
```
Responses:
```json
{
   "40078dc7-5f24-49a6-ad27-070c9c528f6c":{
       "profile_id":"40078dc7-5f24-49a6-ad27-070c9c528f6c",
       "nickname":"Sidelux00"
   },
   "072f0150-f606-4ae7-9041-b17aa5a2b929":{
       "profile_id":"072f0150-f606-4ae7-9041-b17aa5a2b929",
       "nickname":"Sir.Avocado"
   }
}
```
```json
{
   "40078dc7-5f24-49a6-ad27-070c9c528f6c":{
       "profile_id":"40078dc7-5f24-49a6-ad27-070c9c528f6c",
       "nickname":"Sidelux00"
   }
}
```
```json
{
   "072f0150-f606-4ae7-9041-b17aa5a2b929":{
       "profile_id":"072f0150-f606-4ae7-9041-b17aa5a2b929",
       "nickname":"Sir.Avocado"
   }
}
```

### getStats.php:
With this PHP file you can query the stats of any given player by name or uplay id. Whitch stats are returned is definded in the config.php. All stats that can be returned: https://gist.github.com/sidelux/c2724e64acb7e1b8921c11572800f8d4

Examples:
```
https://website.com/api/r6/getStats.php?id=a39c7ad5-3282-467c-bc85-f65b0e61cde4&appcode=test
```

### Optional Arguments:
```
&stats=STATS (e.g. casualpvp_death,casualpvp_kills)
&platform=PLATFORM (Default uplay)
```

Responses:
```json
{
   "players":{
      "a39c7ad5-3282-467c-bc85-f65b0e61cde4":{
         "casualpvp_matchwon":126,
         "casualpvp_kills":723,
         "casualpvp_death":773,
         "casualpvp_matchlost":130,
         "casualpvp_matchplayed":256,
         "casualpvp_timeplayed":204662,
         "nickname":"AE_SeemsLegit",
         "platform":"uplay"
      }
   }
}
```

### getOperators.php:
With this PHP file you can query the stats of all operators by giving player name or player uplay id.

Examples:
```
https://website.com/api/r6/getOperators.php?id=a39c7ad5-3282-467c-bc85-f65b0e61cde4&appcode=test
```

### Optional Arguments:
```
&platform=PLATFORM (Default uplay)
```

Responses (stripped for readability):
```json
{
   "players":{
      "a39c7ad5-3282-467c-bc85-f65b0e61cde4":{
         "capitao":{
            "operatorpvp_roundlost":0,
            "operatorpvp_death":0,
            "operatorpvp_roundwon":0,
            "operatorpvp_kills":0,
            "operatorpvp_timeplayed":0,
            "operatorpvp_capitao_lethaldartkills":0,
            "operatorpve_capitao_lethaldartkills":0
         },
         "zofia":{
            "operatorpvp_roundlost":14,
            "operatorpvp_death":22,
            "operatorpvp_roundwon":16,
            "operatorpvp_kills":18,
            "operatorpvp_timeplayed":5615,
            "operatorpvp_concussiongrenade_detonate":52,
            "operatorpve_concussiongrenade_detonate":0
         },
         ...
         "profile_id":"a39c7ad5-3282-467c-bc85-f65b0e61cde4",
         "nickname":"AE_SeemsLegit",
         "platform":"uplay"
      }
   },
   "operators":{
      "capitao":{
         "images":{
            "badge":"https:\/\/ubistatic-a.akamaihd.net\/0058\/prod\/assets\/images\/badge-capitao.6603e417.png",
            "figure":{
               "small":"https:\/\/ubistatic-a.akamaihd.net\/0058\/prod\/assets\/images\/small-capitao.31c21fd0.png",
               "large":"https:\/\/ubistatic-a.akamaihd.net\/0058\/prod\/assets\/images\/large-capitao.984e75b7.png"
            },
            "mask":"https:\/\/ubistatic-a.akamaihd.net\/0058\/prod\/assets\/images\/mask-capitao.f56d66af.png"
         },
         "category":"atk",
         "index":"2:8",
         "id":"capitao",
         "organisation":"BOPE",
         "name":"Capit\u00e3o"
      },
      "zofia":{
         "images":{
            "badge":"https:\/\/ubistatic-a.akamaihd.net\/0058\/prod\/assets\/images\/badge-zofia.2a892bf5.png",
            "figure":{
               "small":"https:\/\/ubistatic-a.akamaihd.net\/0058\/prod\/assets\/images\/small-zofia.28fa7ba7.png",
               "large":"https:\/\/ubistatic-a.akamaihd.net\/0058\/prod\/assets\/images\/large-zofia.f9f7568b.png"
            },
            "mask":"https:\/\/ubistatic-a.akamaihd.net\/0058\/prod\/assets\/images\/mask-zofia.29e5102f.png"
         },
         "category":"atk",
         "index":"3:C",
         "id":"zofia",
         "organisation":"GROM",
         "name":"Zofia"
      },
      ...
   }
}
```

### Extras Folder:
If there are new available operators, you should launch updateOperators.bat (or .sh, depends on your platform) and it will rewrite Operators.php automatically by executing a java program.\
So, your machine must have java installed to launch executable, and file must have **execute** permission.

Example for unix:
```sh
cd extras
chmod +x updateOperators.sh
./updateOperators.sh
```
When it request operators.json url, put your url or ubi url (https://game-rainbow6.ubi.com/assets/data/operators.682af7ce0969c4ec.json) to start loading data.\
Once finished you can safely delete output.json file and move Operators.php file to parent directory (overwriting old file).\
Manually editing of Operators.php file is *not* recommended.

### Error Handling:
If a function encounter errors like "Too many calls", api add an "error" object in json that contains detailed response from ubisoft servers.

Example if there are too many calls:
```json
{
   "players":[

   ],
   "error":{
      "message":"Too many calls per profile id: 6eb1a73b-e20b-4bcf-92c3-26f046f8a302",
      "errorCode":1100,
      "httpCode":429,
      "errorContext":"Profiles Client Legacy",
      "moreInfo":"6/27/2018 11:10:52 AM",
      "transactionTime":"2018-06-27T10:55:56.8938985Z",
      "transactionId":"a7056a14-faee-4c02-8e13-38ed85399eb2"
   }
}
```

Example if player not found:
```json
{
   "players":[

   ],
   "error":{
      "message":"User not found!"
   }
}
```

## Todo:
- Add config.php - Done
- Kills and deaths - Done
- Operator stats - Done
- User progression stats - Done
- Detailed operators stats - Done

Thanks to Seems2Legit, _sidelux and special thanks to K4CZP3R. They made this whole project even possible.

Updated: 22.10.2018 10:00 UTC
