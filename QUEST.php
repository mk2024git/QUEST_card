<?php
class Card {
    public $suit; 
    public $rank;

    public function __construct($suit, $rank) {
        $this->suit = $suit;
        $this->rank = $rank;
    }

    // カードの情報を返すメソッド
    public function __toString() {
        // 11以上のランクをアルファベット表記で返す
        switch ($this->rank) {
            case 14:
                return $this->suit . "のA";
            case 13:
                return $this->suit . "のK";
            case 12:
                return $this->suit . "のQ";
            case 11:
                return $this->suit . "のJ";
            default:
                return $this->suit . "の" . $this->rank;
        }
    }
}

class Deck {
    public $cards = []; // デッキを保持する配列

    // 52枚のカードを生成
    public function __construct() {
        $suits = array('スペード', 'ハート', 'ダイヤ', 'クラブ');
        $ranks = array(14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2);

        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $this->cards[] = new Card($suit, $rank);
            }
        }
    }

    // デッキをシャッフルするメソッド
    public function shuffle() {
        shuffle($this->cards);
    }

    // デッキが空かどうかをチェックするメソッド
    public function isEmpty() {
        return empty($this->cards);
    }

    // デッキからカードを1枚引くメソッド
    public function drawCard() {
        return array_shift($this->cards);
    }
}

class Player {
    public $hand = []; // プレイヤーの手札を保持する配列
    public $wonCards = []; // プレイヤーが勝ったカードを保持する配列

    // デッキから手札にカードを追加するメソッド
    public function addHand($card) {
        $this->hand[] = $card;
    }

    // 手札からカードをテーブルに出すメソッド
    public function playCard() {
        // 手札からカードを1枚取り出し、そのカードを手札から削除して返す
        return array_shift($this->hand);
    }

    // 場札を貰うメソッド
    public function getWonCards($cards) {
        $this->wonCards = array_merge($this->wonCards, $cards);
    }

    // 手札を取得するメソッド
    public function getHand() {
        return $this->hand;
    }
    
    // 手札が空かどうかをチェックするメソッド
    public function isHandEmpty() {
        return empty($this->hand);
    }
    
    // 手札と手元のカードの合計枚数を返すメソッド
    public function getTotalCards() {
        return count($this->hand) + count($this->wonCards);
    }

    // 手元のカードをシャッフルして手札に加えるメソッド
    public function replenishHand() {
            shuffle($this->wonCards);
            $this->hand = $this->wonCards;
            $this->wonCards = []; // $wonCardsを空にする
    }
}

class Game {
    public $player1;
    public $player2; 
    public $deck;
    public $table = []; // テーブルに出されたカードを保持するための配列
    public $stock = []; // テーブルに出されたカードをストックするための配列

    public function __construct($player1, $player2, $deck) {
        $this->player1 = $player1;
        $this->player2 = $player2;
        $this->deck = $deck;
    }

    // ゲームを継続するかどうかをチェックするメソッド
    public function continueGame() {
        // どちらかのプレイヤーの手持ちのカードが0になった場合、ゲーム終了
        return $this->player1->getTotalCards() > 0 && $this->player2->getTotalCards() > 0; // true && false === false
    }    
    
    // ゲームを開始するメソッド
    public function startGame() {
        echo "ゲームを開始します。" . PHP_EOL;
        // デッキをシャッフル
        $this->deck->shuffle();

        // デッキからカードを交互に配る
        while (!$this->deck->isEmpty()) {   // デッキが空になるまで
            $this->player1->addHand($this->deck->drawCard());
            $this->player2->addHand($this->deck->drawCard());
        }
        echo "カードが配られました。" . PHP_EOL;
    }

    // 手札からカードをテーブルにセットするメソッド
    public function setCard($player) {
        // プレイヤーの手札からカードをテーブルにセット
        $card = $player->playCard();
        if ($card) {
            $this->table[] = $card; // 出されたカードをテーブルに追加
            $this->stock[] = $card; // 出されたカードをストックに追加
        }
    }

    // テーブルにセットしたカードを表示するメソッド
    public function openCard() {
        // プレイヤーがカードをテーブルにセット
        $this->setCard($this->player1);
        $this->setCard($this->player2);

        echo "戦争！" . PHP_EOL;

        // プレイヤーのカードを表示
        echo "プレイヤー1のカードは" . $this->table[0] . PHP_EOL;
        echo "プレイヤー2のカードは" . $this->table[1] . PHP_EOL;
    }   

    // 勝ち負けを決めるメソッド
    public function battle() {
        $card1 = $this->table[0];
        $card2 = $this->table[1];

        // テーブルに出されたカードのランクを比較
        if ($card1->rank > $card2->rank) {
            echo "プレイヤー1が勝ちました。";
            $this->player1->getWonCards($this->stock); // テーブルにストックされたカードをプレイヤー1の手元に追加
            echo "プレイヤー1はカードを" . count($this->stock) . "枚もらいました。" . PHP_EOL;
            $this->table = []; // テーブルのカードを削除
            $this->stock = []; // ストックされたカードを削除
        } elseif ($card1->rank < $card2->rank) {
            echo "プレイヤー2が勝ちました。";
            $this->player2->getWonCards($this->stock);
            echo "プレイヤー2はカードを" . count($this->stock) . "枚もらいました。" . PHP_EOL;
            $this->table = [];
            $this->stock = [];
        } else {
            echo "引き分けです。" . PHP_EOL;
            $this->table = [];
        }
    }

    // ゲームを実行するメソッド
    public function playGame() {
        while ($this->continueGame()) {

            $this->openCard();
            $this->battle();

            // プレイヤーの手札が0枚になった場合、手元のカードをシャッフルして手札に追加
            if ($this->player1->isHandEmpty()) {
                if ($this->player1->getTotalCards() > 0) {
                echo "プレイヤー1の手札が0になったため、手元のカードをシャッフルして手札に加えます。" . PHP_EOL;
                $this->player1->replenishHand();
                }
            }
            if ($this->player2->isHandEmpty()) {
                if ($this->player2->getTotalCards() > 0) {
                echo "プレイヤー2の手札が0になったため、手元のカードをシャッフルして手札に加えます。" . PHP_EOL;
                $this->player2->replenishHand();
                }
            }
        }
        $this->result();
    }

    // ゲームの結果を表示するメソッド
    public function result() {
        // プレイヤーの手札と手元の枚数を取得
        $player1TotalCount = $this->player1->getTotalCards();
        $player2TotalCount = $this->player2->getTotalCards();    

        if ($this->player1->getTotalCards() === 0) {
            echo "プレイヤー1のカードがなくなりました。" . PHP_EOL;
            echo "プレイヤー1の手札の枚数は" . $player1TotalCount . "です。";
            echo "プレイヤー2の手札の枚数は" . $player2TotalCount . "です。" . PHP_EOL;
            echo "プレイヤー2が1位、プレイヤー1が2位です。" . PHP_EOL;
        } elseif ($this->player2->getTotalCards() === 0) {
            echo "プレイヤー2の手札がなくなりました。" . PHP_EOL;
            echo "プレイヤー1の手札の枚数は" . $player1TotalCount . "です。";
            echo "プレイヤー2の手札の枚数は" . $player2TotalCount . "です。" . PHP_EOL;
            echo "プレイヤー1が1位、プレイヤー2が2位です。" . PHP_EOL;            
        } else {
            echo "引き分け" . PHP_EOL;
        }
        echo "戦争を終了します。" . PHP_EOL;
    }
}
// プレイヤーの作成
$player1 = new Player();
$player2 = new Player();

// デッキの作成
$deck = new Deck();

// ゲームの作成
$game = new Game($player1, $player2, $deck);

// ゲーム開始
$game->startGame();
$game->playGame();
?>