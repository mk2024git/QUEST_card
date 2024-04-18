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

    // 手札にカードを追加するメソッド
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
        // プレイヤー1がカードをテーブルにセット
        $this->setCard($this->player1);
        // プレイヤー2がカードをテーブルにセット
        $this->setCard($this->player2);
        echo "戦争！" . PHP_EOL;
        // プレイヤー1のカードを表示
        echo "プレイヤー1のカードは" . $this->table[0] . PHP_EOL;
        // プレイヤー2のカードを表示
        echo "プレイヤー2のカードは" . $this->table[1] . PHP_EOL;
    }   

    // 勝ち負けを決めるメソッド
    public function battle() {

        // テーブルに出されたカードのランクを比較
        $card1 = $this->table[0];
        $card2 = $this->table[1];

        if ($card1->rank > $card2->rank) {
            echo "プレイヤー1が勝ちました。" . PHP_EOL;
            // テーブルにストックされたカードをプレイヤー1の手元に追加
            $this->player1->getWonCards($this->stock);
        } elseif ($card1->rank < $card2->rank) {
            echo "プレイヤー2が勝ちました。" . PHP_EOL;
            // テーブルにストックされたカードをプレイヤー2の手元に追加
            $this->player2->getWonCards($this->stock);
        } else {
            echo "引き分けです。" . PHP_EOL;
            $this->table = [];
            $this->openCard();
            $this->battle();
        }

        // テーブルとストックのリセット
        $this->table = [];
        $this->stock = [];
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
$game->openCard();
$game->battle();
?>