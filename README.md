# Rainbow-Six-Siege-Player-Stats-API
This API queries the Rainbow Six Siege stats from any given player by name or uplayID. Multiple and mixed requests at one are also possible.

## Installation:
1. Clone this whole repo
2. Upload it to your webserver to any location
3. In the config.php change the **EMAIL** and the **PASSWORD** fields to an uplay account of your choice.
4. For your security you will need to set an appcode. It's also located in the config.php and can be changed to a string of your choice (You just have to remember it ;)) WARNING: Obviously you should not use spaces).
5. Optionally you can also set your default region and witch stats you want to display on a request
6. Give the webserver permissions to edit the API_TICKET file.
7. Finished!

## Usage:
### getUser.php:
With this API you can query multiple users at one and it does not matter if you use their names, uplayID's or both. (Get requests).

Here are some examples:
```
https://gassner.online/api/r6/getUser.php?id=735e4640-32d3-484f-ba56-f80030d35337&appcode=test
https://gassner.online/api/r6/getUser.php?name=AE_SeemsLegit&appcode=test
https://gassner.online/api/r6/getUser.php?id=735e4640-32d3-484f-ba56-f80030d35337&name=AE_SeemsLegit&appcode=test
```
As you can see it does not matter if you give the api a name or uplay id. It's just important that you never forget to put the appcode into you request.

### Optional Arguments:
```
&season=SEASON (Default -1)
&region=REGION (Default emea)
```

Here are the example responses from the GET requests mentioned above:
```
{
  "players": {
    "735e4640-32d3-484f-ba56-f80030d35337": {
      "board_id": "pvp_ranked",
      "past_seasons_abandons": 0,
      "update_time": "2018-04-08T22:47:21.650000+00:00",
      "skill_mean": 17.7856229582,
      "abandons": 2,
      "season": 9,
      "region": "emea",
      "profile_id": "735e4640-32d3-484f-ba56-f80030d35337",
      "past_seasons_losses": 10,
      "max_mmr": 2621.04601985,
      "mmr": 1778.56229582,
      "wins": 77,
      "skill_stdev": 3.9493432727,
      "rank": 5,
      "losses": 78,
      "next_rank_mmr": 1800,
      "past_seasons_wins": 10,
      "previous_rank_mmr": 1700,
      "max_rank": 8,
      "nickname": "AE_BadKey"
    }
  }
}

-----------------------------------------------------------------------------------------

{
  "players": {
    "a39c7ad5-3282-467c-bc85-f65b0e61cde4": {
      "board_id": "pvp_ranked",
      "past_seasons_abandons": 0,
      "update_time": "2018-04-08T16:05:13.150000+00:00",
      "skill_mean": 26.2664770288,
      "abandons": 0,
      "season": 9,
      "region": "emea",
      "profile_id": "a39c7ad5-3282-467c-bc85-f65b0e61cde4",
      "past_seasons_losses": 19,
      "max_mmr": 2839.11421816,
      "mmr": 2626.64770288,
      "wins": 34,
      "skill_stdev": 5.28927628982,
      "rank": 13,
      "losses": 31,
      "next_rank_mmr": 2700,
      "past_seasons_wins": 8,
      "previous_rank_mmr": 2500,
      "max_rank": 14,
      "nickname": "AE_SeemsLegit"
    }
  }
}

-----------------------------------------------------------------------------------------

{
  "players": {
    "735e4640-32d3-484f-ba56-f80030d35337": {
      "board_id": "pvp_ranked",
      "past_seasons_abandons": 0,
      "update_time": "2018-04-08T22:47:21.650000+00:00",
      "skill_mean": 17.7856229582,
      "abandons": 2,
      "season": 9,
      "region": "emea",
      "profile_id": "735e4640-32d3-484f-ba56-f80030d35337",
      "past_seasons_losses": 10,
      "max_mmr": 2621.04601985,
      "mmr": 1778.56229582,
      "wins": 77,
      "skill_stdev": 3.9493432727,
      "rank": 5,
      "losses": 78,
      "next_rank_mmr": 1800,
      "past_seasons_wins": 10,
      "previous_rank_mmr": 1700,
      "max_rank": 8,
      "nickname": "AE_BadKey"
    },
    "a39c7ad5-3282-467c-bc85-f65b0e61cde4": {
      "board_id": "pvp_ranked",
      "past_seasons_abandons": 0,
      "update_time": "2018-04-08T16:05:13.150000+00:00",
      "skill_mean": 26.2664770288,
      "abandons": 0,
      "season": 9,
      "region": "emea",
      "profile_id": "a39c7ad5-3282-467c-bc85-f65b0e61cde4",
      "past_seasons_losses": 19,
      "max_mmr": 2839.11421816,
      "mmr": 2626.64770288,
      "wins": 34,
      "skill_stdev": 5.28927628982,
      "rank": 13,
      "losses": 31,
      "next_rank_mmr": 2700,
      "past_seasons_wins": 8,
      "previous_rank_mmr": 2500,
      "max_rank": 14,
      "nickname": "AE_SeemsLegit"
    }
  }
}

```
### getSmallUser.php:
This function only returns the uplayID and the uplay name of any given person(s) (by uid or name). As argument you may only pass appcode.

Examples:
```
https://gassner.online/api/r6/getSmallUser.php?name=AE_SeemsLegit,AE_BadKey&appcode=test
https://gassner.online/api/r6/getSmallUser.php?name=AE_SeemsLegit&appcode=test
https://gassner.online/api/r6/getSmallUser.php?id=735e4640-32d3-484f-ba56-f80030d35337&appcode=test
```
Responses:
```
[
  {
    "profile_id": "a39c7ad5-3282-467c-bc85-f65b0e61cde4",
    "nickname": "AE_SeemsLegit"
  },
  {
    "profile_id": "735e4640-32d3-484f-ba56-f80030d35337",
    "nickname": "AE_BadKey"
  }
]

-----------------------------------------------------------------------------------------

[
  {
    "profile_id": "735e4640-32d3-484f-ba56-f80030d35337",
    "nickname": "AE_BadKey"
  }
]

-----------------------------------------------------------------------------------------

[
  {
    "profile_id": "735e4640-32d3-484f-ba56-f80030d35337",
    "nickname": "AE_BadKey"
  }
]
```

### getStats.php:
With this PHP file you can query the stats of any given player by name or uplay id. Whitch stats are returned is definded in the config.php. All stats that can be returned (+Operator stats): https://gist.github.com/LaxisB/3924cfdc35562b719d1c891cdb895366

Examples:
```
https://gassner.online/api/r6/getStats.php?id=a39c7ad5-3282-467c-bc85-f65b0e61cde4&appcode=test
```

Responses:
```
{
  "players": {
    "a39c7ad5-3282-467c-bc85-f65b0e61cde4": {
      "casualpvp_matchwon": 126,
      "casualpvp_kills": 723,
      "casualpvp_death": 773,
      "casualpvp_matchlost": 130,
      "casualpvp_matchplayed": 256,
      "casualpvp_timeplayed": 204662,
      "nickname": "AE_SeemsLegit"
    }
  }
}
```

## Todo:
- Add config.php - Done
- Kills and deaths - Done
- Operator Stats - Done

Thanks to Seems2Legit and special thanks to K4CZP3R. They made this whole project even possible.

Updated: 28.05.2018 18:50 UTC
