<?php

Class phpMarkov {
	
	private function breakSentences($text) {
		$sentences = preg_split('/(?<=[.?!])\s+(?=[a-z|а-я])/i', $text);
		return($sentences);
	}
	
	private function determineNextWord($wordOdds) {
		$oddSum = array_sum($wordOdds);
		$dice = mt_rand(1, $oddSum);
		$tempSum = 1;
		foreach($wordOdds as $key => $odd) {
			$tempSum2 = $tempSum+$odd;
			if($dice >= $tempSum && $dice < $tempSum2) {
				return $key;
			}
			$tempSum = $tempSum2;
		}
	}
	
	public function buildChains($text) {
		$sentences = $this->breakSentences($text);
		$chains = Array();
		$firstWords = Array(); // Along with chains, we need to pass the generator a list of words that occur in the beginning of the sentence.
		foreach($sentences as $k => $sentence) {
			// This breaks down a sentence into words and punctuation. Current regular expression is temporary, as it does not deal with some punctuation at the moment, namely brackets and dashes. 
			$units = preg_split("/\s+|\b(?=[!\?\.\,\;])(?!\.\s+)/", $sentence);
			
			// Here we add the first unit into the list of first words. Might want to include the chances later on.
			if(!in_array($units[0], $firstWords)) {
				array_push($firstWords, $units[0]);
			}
			
			// Here we build chains. Chains here are words with a set of words that ever succeeded them, each successor is accompanied with a number of times it succeeded its predeccessor.
			$sentenceUnits = count($units);
			foreach($units as $key => $unit) { // Iteration over the sentence
				// Check if the word is the last one, because if it is, then we don't add any successors
				if((count($units)-1) == $key) {
					$successor = FALSE; // Later on, if $successor is FALSE, then we do not handle successors
				} else {
					// Determine units successor
					$successor = $units[$key+1];
				}
				if(!array_search($unit, array_column($chains, 'unit'))) { // Check if the unit is already a primary chain
					// If it's not, then create a primary chain
					$chainKey = count($chains); // This is a very bad way to do this, but it works. Count counts the number of array elements starting from 1; array keys are offset by one (they start at zero), so when we need to determine a key for a new element, just count() works fine.
					$chains[$chainKey]['unit'] = $unit;
					if($successor != FALSE) {
						// And add the successor into a chain
						$chains[$chainKey]['successors'][0]['successor'] = $successor;
						$chains[$chainKey]['successors'][0]['appearances'] = 1;
					}
				} else {
					if($successor != FALSE) {
						// If the unit is already a primary chain, then proceed to check if the successor is already there
						$chainKey = array_search($unit, array_column($chains, 'unit'));
						$successorKey = array_search($successor, array_column($chains[$chainKey]['successors'], 'successor')); // Look for successor key. If the successor isn't there at all, then this returns FALSE, hence the following expression
						if($successorKey != FALSE) {
							$chains[$chainKey]['successors'][$successorKey]['appearances']++; // since its already there, we only need to increase the number of times it appeared as a successor
						} else {
							$successorKey = count($chains[$chainKey]['successors']); // As before, this is a dirty way to determine a key for the next element.
							$chains[$chainKey]['successors'][$successorKey]['successor'] = $successor;
							$chains[$chainKey]['successors'][$successorKey]['appearances'] = 1; // This successor is initialized right now, so it only appeared once at this stage
						}
					}
				}
			}
		}
		return array($chains, $firstWords);
	}
	
	public function generateText($chains, $sentences=10, $sentenceMinLength=5, $sentenceMaxLength=20, $ws=' ', $debug=0) {
		$firstWords = $chains[1];
		$chains = $chains[0];
		$text = '';
		$firstWordsLastElement = count($firstWords)-1;
		// Start sentences builder.
		for ($n = 0; $n < $sentences; $n++) {
			$sentence = '';
			$currentWord = $firstWords[mt_rand(0, count($firstWords)-1)]; // Set a current word in a loop, here it's the first word of the sentence, chosen randomly.
			$sentence .= $currentWord.$ws; // Append the first word and a whitespace.
			$wordCount = 1;
			$endLoop = 0;
			for ($k = 1; $k < $sentenceMaxLength; $k++) {
				// Find the current word's chain
				$chainKey = array_search($currentWord, array_column($chains, 'unit'));
				// Start looking for the successor
				if(isset($chains[$chainKey]['successors'])) {
					$successors = $chains[$chainKey]['successors'];
					$totalSuccessors = count($successors);
					if($totalSuccessors == 1) { // If there are successors, but only one, then just go with it without any further logic
						$nextWord = $successors[0]['successor'];
					} else { // But if there are multiple successors, then determine which one to use by weight.
						$wordOdds = Array(); // To determine it we need to call a function with an array of odds, sorted exactly as successors in $chains array are.
						foreach($successors as $successor) {
							array_push($wordOdds, $successor['appearances']);
						}
						$nextWord = $successors[$this->determineNextWord($wordOdds)]['successor'];
					}
					$sentence .= $nextWord.$ws; // Append the next word to the sentence.
					$currentWord = $nextWord;
					$wordCount++;
				} else {
					// If there's no successor, then end the loop.
					break;
				}
			}
			if($wordCount >= $sentenceMinLength) {
				$text .= $sentence;
			} else {
				$n--;
			}
		}
		return $text;
	}
	
}


