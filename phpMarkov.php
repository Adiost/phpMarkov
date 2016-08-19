<?php

Class phpMarkov {
	
	private function breakSentences($text) {
		$sentences = preg_split('/(?<=[.?!])\s+(?=[a-z|а-я])/i', $text);
		return($sentences);
	}
	
	public function buildChains($text) {
		$sentences = $this->breakSentences($text);
		$chains = Array();
		foreach($sentences as $k => $sentence) {
			// This breaks down a sentence into words and punctuation. Current regular expression is temporary, as it does not deal with some punctuation at the moment, namely brackets and dashes. 
			$units = preg_split("/\s+|\b(?=[!\?\.\,\;])(?!\.\s+)/", $sentence);
			
			// Here we build chains. Chains here are words with a set of words that ever succeeded them, each successor is accompanied with a number of times it succeeded its predeccessor.
			$sentenceUnits = count($units);
			foreach($units as $unit => $key) { // Iteration over the sentence
				// Determine units successor
				$successor = $units[$key+1];
				if(!in_array($unit, $chains)) { // Check if the unit is already a primary chain
					// If it's not, then create a primary chain
					array_push($chains, $unit);
					// And add the successor into a chain. Successors are written as keys, number of times they appeared as values.
					$chains[$unit]["'$successor'"] = 1; // So apparently you can't just set a string as an array key, thanks, StackOverflow. Somebody please put this line out of its misery.
				} else {
					// If the unit is already a primary chain, then proceed to check if the successor is already there
					if(isset($chains[$unit][$successor])) {
						$chains[$unit]["'$successor'"]++; // since its already there, we only need to increase the number of times it appeared as a successor
					} else {
						$chains[$unit]["'$successor'"] = 1;
					}
				}
			}
		}
		return $chains;
	}
	
	public function generateText() {
	}
	
}

$text1="Even as dawn approached, the number of moons didn’t increase. It was just the same old familiar moon. The one and only satellite that has faithfully circled the earth, at the same speed, from before human memory. As she stared at the moon, Aomame softly touched her abdomen, checking one more time that the little one was there, inside her. She could swear her belly had grown from the night before. I still don’t know what sort of world this is , she thought. But whatever world we’re in now, I’m sure this is where I will stay. Where we will stay. This world must have its own threats, its own dangers, must be filled with its own type of riddles and contradictions. We may have to travel down many dark paths, leading who knows where. But that’s okay. It’s not a problem. I’ll just have to accept it. I’m not going anywhere. Come what may, this is where we’ll remain, in this world with one moon. The three of us—Tengo and me, and the little one.";

$text2='Я вам рассказал так подробно об астероиде Б-612 и даже сообщил его номер только из-за взрослых. Взрослые очень любят цифры. Когда рассказываешь им, что у тебя появился новый друг, они никогда не спросят о самом главном. Никогда они не скажут: "А какой у него голос? В какие игры он любит играть? Ловит ли он бабочек?" Они спрашивают: "Сколько ему лет? Сколько у него братьев? Сколько он весит? Сколько зарабатывает его отец?" И после этого воображают, что узнали человека. Когда говоришь взрослым: "Я видел красивый дом из розового кирпича, в окнах у него герань, а на крыше голуби", - они никак не могут представить себе этот дом. Им надо сказать: "Я видел дом за сто тысяч франков", - и тогда они восклицают: "Какая красота!" Точно так же, если им сказать: "Вот доказательства, что Маленький принц на самом деле существовал - он был очень, очень славный, он смеялся, и ему хотелось иметь барашка. А кто хочет барашка, тот уж конечно существует", - если сказать так, они только пожмут плечами и посмотрят на тебя как на несмышленого младенца. Но если сказать им: "Он прилетел с планеты, которая называется астероид Б-612", - это их убедит, и они не станут докучать вам расспросами. Уж такой народ эти взрослые. Не стоит на них сердиться. Дети должны быть очень снисходительны к взрослым. Но мы, те, кто понимает, что такое жизнь, - мы, конечно, смеемся над номерами и цифрами! Я охотно начал бы эту повесть как волшебную сказку. Я хотел бы начать так: "Жил да был Маленький принц. Он жил на планете, которая была чуть побольше его самого, и ему очень не хватало друга..." Те, кто понимает, что такое жизнь, сразу увидели бы, что это гораздо больше похоже на правду. Ибо я совсем не хочу, чтобы мою книжку читали просто ради забавы. Сердце мое больно сжимается, когда я вспоминаю моего маленького друга, и нелегко мне о нем говорить. Вот уже шесть лет, как мой друг вместе с барашком меня покинул. И я пытаюсь рассказать о нем для того, чтобы его не забыть. Это очень печально, когда забывают друзей. Не у всякого был друг. И я боюсь стать таким, как взрослые, которым ничто не интересно, кроме цифр. Еще и потому я купил ящик с красками и цветные карандаши. Не так это просто - в моем возрасте вновь приниматься за рисование, если за всю свою жизнь только и нарисовал что удава снаружи и изнутри, да и то в шесть лет! Конечно, я стараюсь передать сходство как можно лучше. Но я совсем не уверен, что у меня это получится. Один портрет выходит удачно, а другой ни капли не похож. Вот и с ростом то же: на одном рисунке принц у меня чересчур большой, на другом - чересчур маленький. И я плохо помню, какого цвета была его одежда. Я пробую рисовать и так и эдак, наугад, с грехом пополам. Наконец, я могу ошибиться и в каких-то важных подробностях. Но вы уж не взыщите. Мой друг никогда мне ничего не объяснял. Может быть, он думал, что я такой же, как он. Но я, к сожалению, не умею увидеть барашка сквозь стенки ящика. Может быть, я немного похож на взрослых. Наверно, я старею.';

$test = new phpMarkov();
var_dump($test->buildChains($text1));
//var_dump($test->breakSentences($text2));
