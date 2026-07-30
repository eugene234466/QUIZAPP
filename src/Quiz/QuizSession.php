<?php 
class QuizSession{
    const SESSION_KEY = 'quiz_session';

    static function store($topic, $full_quiz){
        $_SESSION[self::SESSION_KEY] = [
            'topic' => $topic,
            'full_quiz' => $full_quiz,
            'started_at' => time()
        ];
    }

    static function get(){
        if(!isset($_SESSION[self::SESSION_KEY])){
            return null;
        }
        return $_SESSION[self::SESSION_KEY];
    }
    static function get_questions(){
        $session = self::get();
        if(!$session){
            return null;
        }
        return $session['full_quiz']['questions'];
    }
    static function get_topic(){
        $session = self::get();
        return $session["topic"] ?? null;
    }

    static function elapsed_seconds(){
    $session = self::get();
    if ($session === null) {
        return null;
    }
    return time() - $session['started_at'];
}

    static function clear(){
        unset($_SESSION[self::SESSION_KEY]);
    }

    static function exists(){
        return isset($_SESSION[self::SESSION_KEY]);
    }
}



?>