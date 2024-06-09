<?php

function PutPermanentIntoPlay($player, $cardID)
{
  $permanents = &GetPermanents($player);
  array_push($permanents, $cardID);
  return count($permanents) - PermanentPieces();
}

function RemovePermanent($player, $index)
{
  $index = intval($index);
  $permanents = &GetPermanents($player);
  $cardID = $permanents[$index];
  for ($j = $index + PermanentPieces() - 1; $j >= $index; --$j) {
    unset($permanents[$j]);
  }
  $permanents = array_values($permanents);
  return $cardID;
}

function DestroyPermanent($player, $index)
{
  if($index == -1) return;
  $index = intval($index);
  $permanents = &GetPermanents($player);
  $cardID = $permanents[$index];
  $isToken = $permanents[$index + 4] == 1;
  PermanentDestroyed($player, $cardID, $isToken);
  for ($j = $index + PermanentPieces() - 1; $j >= $index; --$j) {
    unset($permanents[$j]);
  }
  $permanents = array_values($permanents);
}

function PermanentDestroyed($player, $cardID, $isToken = false)
{
  $permanents = &GetPermanents($player);
  for ($i = 0; $i < count($permanents); $i += PermanentPieces()) {
    switch ($permanents[$i]) {
      default:
        break;
    }
  }
  $goesWhere = GoesWhereAfterResolving($cardID);
  if (CardType($cardID) == "T" || $isToken) return; //Don't need to add to anywhere if it's a token
  switch ($goesWhere) {
    case "GY":
      AddGraveyard($cardID, $player, "PLAY");
      break;
    case "SOUL":
      AddSoul($cardID, $player, "PLAY");
      break;
    case "BANISH":
      BanishCardForPlayer($cardID, $player, "PLAY", "NA");
      break;
    default:
      break;
  }
}

function PermanentBeginEndPhaseEffects()
{
  global $mainPlayer, $defPlayer;
  $permanents = &GetPermanents($mainPlayer);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($permanents[$i]) {

      default:
        break;
    }
    if ($remove == 1) DestroyPermanent($mainPlayer, $i);
  }

  $permanents = &GetPermanents($defPlayer);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($permanents[$i]) {

      default:
        break;
    }
    if ($remove == 1) DestroyPermanent($defPlayer, $i);
  }
}

function PermanentTakeDamageAbilities($player, $damage, $type)
{
  $permanents = &GetPermanents($player);
  $otherPlayer = $player == 1 ? 1 : 2;
  //CR 2.1 6.4.10f If an effect states that a prevention effect can not prevent the damage of an event, the prevention effect still applies to the event but its prevention amount is not reduced. Any additional modifications to the event by the prevention effect still occur.
  $preventable = CanDamageBePrevented($otherPlayer, $damage, $type);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($permanents[$i]) {
      case "UPR439":
        if ($damage > 0) {
          if ($preventable) $damage -= 4;
          $remove = 1;
        }
        break;
      case "UPR440":
        if ($damage > 0) {
          if ($preventable) $damage -= 3;
          $remove = 1;
        }
        break;
      case "UPR441":
        if ($damage > 0) {
          if ($preventable) $damage -= 2;
          $remove = 1;
        }
        break;
      default:
        break;
    }
    if ($remove == 1) {
      if (HasWard($permanents[$i]) && SearchCharacterActive($player, "DYN213") && CardType($permanents[$i]) != "T") {
        $index = FindCharacterIndex($player, "DYN213");
        $char[$index + 1] = 1;
        GainResources($player, 1);
      }
      DestroyPermanent($player, $i);
    }
  }
  if ($damage <= 0) $damage = 0;
  return $damage;
}

function PermanentStartTurnAbilities()
{
  global $mainPlayer, $defPlayer;

  $permanents = &GetPermanents($mainPlayer);
  $defPermanents = &GetPermanents($defPlayer);
  $character = &GetPlayerCharacter($mainPlayer);
  $hand = &GetHand($mainPlayer);
  /*WriteLog("size of hand = " . count($hand));
  WriteLog("hand[0] = " . $hand[0]);*/
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($permanents[$i]) {
      case "ROGUE502":
        array_push($hand, $hand[rand(0, count($hand)-1)]);
        break;
      case "ROGUE503":
        $choices = array("WTR098", "WTR099", "WTR100");
        array_push($hand, $choices[rand(0, count($choices)-1)]);
        break;
      case "ROGUE504":
        for($j = 0; $j < count($character)-1; ++$j)
        {
          if(CardType($character[$j]) == "W") $character[$j + 3] += 1;
        }
        break;
      case "ROGUE505":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE506":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE507":
        $items = &GetItems($mainPlayer);
        $found = false;
        for($j = 0; $j < count($items)-1; ++$j) { if($items[$j] == "DYN243") $found = true; continue; }
        if(!$found) AddDecisionQueue("STARTOFGAMEPUTPLAY", 1, "DYN243");
        break;
      case "ROGUE509":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        array_unshift($hand, "DYN065");
        break;
      case "ROGUE511":
        MayBottomDeckDraw();
        break;
      case "ROGUE512": case "ROGUE513":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE517":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE518":
        AddDecisionQueue("FINDINDICES", $mainPlayer, "HAND");
        AddDecisionQueue("MAYCHOOSEHAND", $mainPlayer, "<-", 1);
        AddDecisionQueue("ROGUEMIRRORTURNSTART", $mainPlayer, "0");
        break;
      case "ROGUE519":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE521":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE522":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE525":
        $resources = &GetResources($mainPlayer);
        $deck = &GetDeck($mainPlayer);
        $discard = &GetDiscard($mainPlayer);
        $resources[0] += ((count($deck) + count($discard) + count($hand)) - (count($deck) + count($discard) + count($hand))%10)/10;
        break;
      case "ROGUE526":
        global $currentRound;
        $deckOne = &GetDeck($mainPlayer);
        $deckTwo = &GetDeck($defPlayer);
        if($currentRound== 10) $deckOne = $deckTwo = [];
        break;
      case "ROGUE527":
        $trinkets = array(
          "WTR162", "WTR170", "WTR171", "WTR172",
          "ARC163",
          "MON302",
          "EVR176", "EVR177", "EVR178", "EVR179", "EVR180", "EVR181", "EVR182", "EVR183", "EVR184", "EVR185", "EVR186", "EVR187", "EVR188", "EVR189", "EVR190", "EVR191", "EVR192", "EVR193"
        );
        AddDecisionQueue("STARTOFGAMEPUTPLAY", 1, $trinkets[rand(0, count($trinkets)-1)]);
        break;
      default:
        break;
    }
  }
  for ($i = count($defPermanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($defPermanents[$i]) {
      case "ROGUE506":
        AddCurrentTurnEffect($defPermanents[$i], $defPlayer);
        break;
      case "ROGUE523":
        AddCurrentTurnEffect($defPermanents[$i], $mainPlayer);
        break;

      case "ROGUE709":
        AddCurrentTurnEffect($defPermanents[$i], $mainPlayer);
        break;
      case "ROGUE802":
        AddCurrentTurnEffect($defPermanents[$i], $defPlayer);
        break;
      default:
        break;
    }
  }
}

function PermanentPlayAbilities($attackID, $from="")
{
  global $mainPlayer, $actionPoints;
  $permanents = &GetPermanents($mainPlayer);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch($permanents[$i]) {

      default:
        break;
    }
  }
}

function PermanentAddAttackAbilities()
{
  global $mainPlayer;
  $amount = 0;
  $permanents = &GetPermanents($mainPlayer);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    switch($permanents[$i]) {
      case "ROGUE705":
        $amount += 1;
        break;
      default:
        break;
    }
  }
  return $amount;
}

function PermanentDrawCardAbilities($player)
{
  global $mainPlayer, $defPlayer, $currentPlayer;
  $permanents = &GetPermanents($mainPlayer);
  for($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    switch($permanents[$i]) {
      case "ROGUE601":
        if($mainPlayer == $player) AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      default:
        break;
    }
  }
}

?>
