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
		return $chains;
	}
	
	public function generateText() {
	}
	
}


