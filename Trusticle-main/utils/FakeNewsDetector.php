<?php
/**
 * FakeNewsDetector Class
 * Advanced multi-factor fake news detection and scoring system
 */
class FakeNewsDetector {
    private $conn;
    private $keywords = [];
    private $minScore = 0;
    private $maxScore = 100;
    
    // Weighting factors for the scoring components
    private $weights = [
        'density' => 0.35,       // Weight for keyword density
        'variety' => 0.30,       // Weight for keyword variety
        'clustering' => 0.20,    // Weight for keyword clustering
        'position' => 0.15       // Weight for keyword positions
    ];
    
    /**
     * Constructor
     * @param mysqli $conn - Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
        $this->loadKeywords();
    }
    
    /**
     * Load fake news keywords from database
     */
    private function loadKeywords() {
        $query = "SELECT keyword FROM fake_keywords";
        $result = $this->conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $this->keywords[] = strtolower($row['keyword']);
            }
        }
    }
    
    /**
     * Advanced analysis of article content for fake news indicators
     * @param string $content - The article content
     * @return array - Results of the analysis
     */
    public function analyzeArticle($content) {
        // Convert to lowercase for case-insensitive matching
        $content = strtolower($content);
        
        // Split content into sentences for positional and clustering analysis
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $totalSentences = count($sentences);
        
        // Calculate word count
        $words = preg_split('/\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);
        
        // If content is very short, normalize to prevent artificially high density
        $normalizedWordCount = ($wordCount < 50) ? max(50, $wordCount * 1.5) : $wordCount;
        
        // Initialize tracking variables
        $matches = [];
        $matchCount = 0;
        $keywordPositions = [];
        $sentenceMatches = array_fill(0, max(1, $totalSentences), 0);
        
        // Analyze each keyword
        foreach ($this->keywords as $keyword) {
            // Use word boundary for better matching
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
            preg_match_all($pattern, $content, $keywordMatches, PREG_OFFSET_CAPTURE);
            
            $occurrences = count($keywordMatches[0]);
            
            if ($occurrences > 0) {
                // Store matches
                $currentMatches = [];
                $positions = [];
                
                foreach ($keywordMatches[0] as $match) {
                    $currentMatches[] = $match[0]; // The matched text
                    $positions[] = $match[1];      // Position in the content
                    
                    // Track positions for clustering analysis
                    $keywordPositions[] = $match[1];
                }
                
                // Calculate keyword score with diminishing returns for repetition
                // First occurrences have higher impact than subsequent ones
                $keywordWeight = 1 + log10($occurrences);
                
                // Track sentence-level occurrences for clustering analysis
                foreach ($sentences as $idx => $sentence) {
                    if (preg_match($pattern, $sentence)) {
                        $sentenceMatches[$idx]++;
                    }
                }
                
                $matches[] = [
                    'keyword' => $keyword,
                    'count' => $occurrences,
                    'matches' => $currentMatches,
                    'positions' => $positions,
                    'weight' => $keywordWeight
                ];
                
                $matchCount += $occurrences;
            }
        }
        
        // FACTOR 1: Keyword Density Score (normalized by word count)
        $uniqueKeywordCount = count($matches);
        $densityScore = $this->calculateDensityScore($matchCount, $normalizedWordCount, $uniqueKeywordCount);
        
        // FACTOR 2: Keyword Variety Score (unique keywords relative to total possible)
        $varietyScore = $this->calculateVarietyScore($uniqueKeywordCount);
        
        // FACTOR 3: Keyword Clustering Score (analyze distribution and concentration)
        $clusteringScore = $this->calculateClusteringScore($sentenceMatches, $totalSentences);
        
        // FACTOR 4: Keyword Position Score (earlier = more important)
        $positionScore = $this->calculatePositionScore($keywordPositions, strlen($content));
        
        // Combine all factors with their respective weights
        $combinedScore = (
            $densityScore * $this->weights['density'] +
            $varietyScore * $this->weights['variety'] +
            $clusteringScore * $this->weights['clustering'] +
            $positionScore * $this->weights['position']
        ) * 100; // Scale to 0-100
        
        // Apply progressive capping based on unique keyword count
        $finalScore = $this->applyProgressiveCapping($combinedScore, $uniqueKeywordCount);
        
        // Determine prediction based on final score
        $prediction = $this->determinePrediction($finalScore);
        
        // Return comprehensive analysis results
        return [
            'matches' => $matches,
            'match_count' => $matchCount,
            'unique_keywords' => $uniqueKeywordCount,
            'score' => round($finalScore, 1),
            'prediction' => $prediction,
            'metrics' => [
                'density_score' => round($densityScore * 100, 1),
                'variety_score' => round($varietyScore * 100, 1),
                'clustering_score' => round($clusteringScore * 100, 1),
                'position_score' => round($positionScore * 100, 1)
            ]
        ];
    }
    
    /**
     * Calculate density score based on matches and word count
     */
    private function calculateDensityScore($matchCount, $wordCount, $uniqueKeywordCount) {
        if ($wordCount <= 0 || $matchCount <= 0) {
            return 0;
        }
        
        // Base density score - keywords per word with diminishing returns
        $baseDensity = min(0.2, $matchCount / $wordCount);
        
        // Transform with logarithmic scaling to prevent extreme values
        // and apply soft cap that becomes harder to reach as density increases
        return 0.7 * $baseDensity + 0.3 * log10(1 + $baseDensity * 10);
    }
    
    /**
     * Calculate variety score based on number of unique keywords matched
     */
    private function calculateVarietyScore($uniqueKeywordCount) {
        if ($uniqueKeywordCount <= 0) {
            return 0;
        }
        
        // Total possible keywords (could be configurable or count from DB)
        $totalPossibleKeywords = max(count($this->keywords), 1);
        
        // Base variety metric - ratio of found keywords to total possible
        $baseVariety = min(0.8, $uniqueKeywordCount / $totalPossibleKeywords);
        
        // Apply logarithmic scaling for more balanced distribution
        return 0.5 * $baseVariety + 0.5 * log10(1 + $uniqueKeywordCount) / log10(10);
    }
    
    /**
     * Calculate clustering score based on sentence-level keyword concentrations
     */
    private function calculateClusteringScore($sentenceMatches, $totalSentences) {
        if ($totalSentences <= 0) {
            return 0;
        }
        
        // Count sentences with at least one keyword
        $sentencesWithKeywords = 0;
        $maxKeywordsInSentence = 0;
        
        foreach ($sentenceMatches as $count) {
            if ($count > 0) {
                $sentencesWithKeywords++;
                $maxKeywordsInSentence = max($maxKeywordsInSentence, $count);
            }
        }
        
        if ($sentencesWithKeywords <= 0) {
            return 0;
        }
        
        // Calculate clustering ratio - higher if keywords are concentrated
        $clusteringRatio = min(1, $maxKeywordsInSentence / 3);
        
        // Calculate distribution - higher if keywords appear throughout the text
        $distributionRatio = min(1, $sentencesWithKeywords / $totalSentences);
        
        // Combined score weights both concentration and distribution
        return 0.6 * $clusteringRatio + 0.4 * $distributionRatio;
    }
    
    /**
     * Calculate position score based on where keywords appear
     * Keywords appearing earlier in the content have more weight
     */
    private function calculatePositionScore($positions, $contentLength) {
        if (empty($positions) || $contentLength <= 0) {
            return 0;
        }
        
        // Calculate normalized positions (0 = start, 1 = end)
        $normalizedPositions = array_map(function($pos) use ($contentLength) {
            return $pos / $contentLength;
        }, $positions);
        
        // Calculate average position, with early positions weighted more heavily
        $weightedSum = 0;
        $totalWeight = 0;
        
        foreach ($normalizedPositions as $pos) {
            // Inverse weighting - earlier positions get higher weight
            $weight = 1 - (0.8 * $pos);
            $weightedSum += $pos * $weight;
            $totalWeight += $weight;
        }
        
        $avgPosition = $totalWeight > 0 ? $weightedSum / $totalWeight : 0.5;
        
        // Convert to score (lower positions = higher score)
        return 1 - $avgPosition;
    }
    
    /**
     * Apply progressive capping to prevent high scores with few keywords
     */
    private function applyProgressiveCapping($score, $uniqueKeywordCount) {
        // Apply caps based on unique keyword count
        if ($uniqueKeywordCount <= 0) {
            return 0;
        } else if ($uniqueKeywordCount == 1) {
            return min(30, $score); // Single keyword can't exceed 30%
        } else if ($uniqueKeywordCount <= 3) {
            return min(50, $score); // 2-3 keywords can't exceed 50%
        } else if ($uniqueKeywordCount <= 5) {
            return min(75, $score); // 4-5 keywords can't exceed 75%
        } else {
            return min(100, $score); // 6+ keywords can reach full scale
        }
    }
    
    /**
     * Determine prediction category based on score
     */
    private function determinePrediction($score) {
        if ($score < 25) {
            return 'legit';
        } else if ($score < 50) {
            return 'suspicious';
        } else {
            return 'fake';
        }
    }
    
    /**
     * Highlight fake keywords in the article content
     * @param string $content - Original article content
     * @param array $matches - Keyword matches from analyzeArticle
     * @return string - Content with highlighted keywords
     */
    public function highlightKeywords($content, $matches) {
        if (empty($matches)) {
            return $content;
        }
        
        // Create a pattern of all keywords to match
        $keywords = [];
        foreach ($matches as $match) {
            $keywords[] = preg_quote($match['keyword'], '/');
        }
        
        if (empty($keywords)) {
            return $content;
        }
        
        $pattern = '/\b(' . implode('|', $keywords) . ')\b/i';
        
        // Replace with highlighted version
        $highlightedContent = preg_replace(
            $pattern, 
            '<span class="fake-keyword" title="This keyword may indicate fake news">$1</span>', 
            $content
        );
        
        return $highlightedContent ?: $content;
    }
} 