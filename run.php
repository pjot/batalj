<?php

class Card
{
    const TIE  = 0;
    const WIN  = 1;
    const LOSS = 2;

    protected static $valueMap = [
        'J' => 11,
        'Q' => 12,
        'K' => 13,
        'A' => 14,
    ];

    protected static $values;
    public $suit;
    public $value;

    public function __construct($suit, $value)
    {
        $this->suit = $suit;
        $this->value = $value;
    }

    public static function getSuits()
    {
        return ['C', 'H', 'D', 'S'];
    }

    public function getValue()
    {
        if (isset(self::$valueMap[$this->value]))
            return self::$valueMap[$this->value];

        return $this->value;
    }

    public static function getValues()
    {
        if ( ! isset(self::$values))
        {
            self::$values = [];
            for ($i = 2; $i < 11; $i++)
                self::$values[] = $i;

            foreach (self::$valueMap as $key => $value)
                self::$values[] = $key;
        }
        return self::$values;
    }

    public function __toString()
    {
        return $this->value . $this->suit;
    }

    public function compare(Card $card)
    {
        if ($card->value == $this->value)
            return self::TIE;

        return $this->getValue() > $card->getValue() ? self::WIN : self::LOSS;
    }
}

class Deck
{
    public $cards = [];

    public function __construct()
    {
        foreach (Card::getSuits() as $suit)
            foreach (Card::getValues() as $value)
                $this->cards[] = new Card($suit, $value);
    }

    public function shuffle()
    {
        shuffle($this->cards);
    }

    public function deal()
    {
        $pile_1 = new Pile();
        $pile_2 = new Pile();
        while (count($this->cards) > 0)
        {
            $pile_1->addCard(array_pop($this->cards));
            $pile_2->addCard(array_pop($this->cards));
        }
        return [$pile_1, $pile_2];
    }
}

class Pile
{
    public $cards = [];

    public function addCard(Card $card)
    {
        $this->cards[] = $card;
    }

    public function hasCards()
    {
        return count($this->cards) > 0;
    }

    public function getCard()
    {
        return array_shift($this->cards);
    }
}

class Batalj
{
    public $piles = [];
    public $battles = 0;
    public $bataljs = 0;
    public $echo = false;

    public function __construct($piles)
    {
        $this->piles = $piles;
    }

    protected function canPlay()
    {
        foreach ($this->piles as $pile)
            if ( ! $pile->hasCards())
                return false;

        return true;
    }

    public function play()
    {
        $cards = [];
        while ($this->canPlay())
        {
            if ($this->echo && $this->battles % 500 === 0)
                printf("Battles: %s\n", $this->battles);

            $card_1 = $this->piles[0]->getCard();
            $card_2 = $this->piles[1]->getCard();
            $cards[] = $card_1;
            $cards[] = $card_2;
            switch ($card_1->compare($card_2))
            {
                case Card::TIE:
                    $cards[] = $this->piles[0]->getCard();
                    $cards[] = $this->piles[1]->getCard();
                    $this->bataljs++;
                    if ($this->echo)
                        printf("BATALJ: %s-%s\n",
                            count($this->piles[0]->cards),
                            count($this->piles[1]->cards)
                        );

                    break;
                case Card::WIN:
                    shuffle($cards);
                    foreach ($cards as $card)
                        $this->piles[0]->addCard($card);

                    $cards = [];
                    $this->battles++;
                    break;
                case Card::LOSS:
                    shuffle($cards);
                    foreach ($cards as $card)
                        $this->piles[1]->addCard($card);

                    $cards = [];
                    $this->battles++;
                    break;
            }
        }
        if ($this->echo)
            printf("Rounds: %s. Bataljs: %s\n", $this->battles, $this->bataljs);
    }
}

$battles = 0;
$bataljs = 0;
$max_battles = 0;
$max_bataljs = 0;
$min_battles = 100000;
$min_bataljs = 100000;

$game_count = isset($argv[1]) ? $argv[1] : 10000;

echo "Games: ";
for ($i = 0; $i < $game_count; $i++)
{
    if ($i % 100 === 0)
        echo "$i...";

    $deck = new Deck();
    $deck->shuffle();

    $game = new Batalj($deck->deal());
    $game->play();
    $battles += $game->battles;
    $bataljs += $game->bataljs;
    if ($game->battles > $max_battles)
        $max_battles = $game->battles;

    if ($game->bataljs > $max_bataljs)
        $max_bataljs = $game->bataljs;

    if ($game->battles < $min_battles)
        $min_battles = $game->battles;

    if ($game->bataljs < $min_bataljs)
        $min_bataljs = $game->bataljs;

}
printf("$game_count!\n");
printf("Average battles: %s. Average bataljs: %s\n",
    floor($battles / $game_count), 
    floor($bataljs / $game_count)
);
printf("Most battles: %s. Most bataljs: %s\n",
    $max_battles,
    $max_bataljs
);
printf("Least battles: %s. Least bataljs: %s\n",
    $min_battles,
    $min_bataljs
);
