<?php

namespace App\Services;

/**
 * Multi-Layer Perceptron (MLP) Neural Network
 * A proper AI model for rice yield prediction
 */
class NeuralNetwork
{
	private array $weights = [];
	private array $biases = [];
	private int $inputSize;
	private array $hiddenSizes;
	private int $outputSize;
	private float $learningRate;
	private int $epochs;
	private array $featureMean = [];
	private array $featureStd = [];
	private float $targetMean = 0.0;
	private float $targetStd = 1.0;
	private int $randomSeed = 42; // Default seed for reproducibility

	public function __construct(int $inputSize = 5, array $hiddenSizes = [10, 8], float $learningRate = 0.01, int $epochs = 1000)
	{
		$this->inputSize = $inputSize;
		$this->hiddenSizes = $hiddenSizes;
		$this->outputSize = 1;
		$this->learningRate = $learningRate;
		$this->epochs = $epochs;
	}

	private function initializeWeights(int $seed): void
	{
		mt_srand($seed);
		$sizes = array_merge([$this->inputSize], $this->hiddenSizes, [$this->outputSize]);
		
		for ($i = 0; $i < count($sizes) - 1; $i++) {
			$rows = $sizes[$i + 1];
			$cols = $sizes[$i];
			
			// Xavier initialization
			$limit = sqrt(6.0 / ($rows + $cols));
			$this->weights[$i] = [];
			$this->biases[$i] = [];
			
			for ($r = 0; $r < $rows; $r++) {
				$this->weights[$i][$r] = [];
				for ($c = 0; $c < $cols; $c++) {
					$this->weights[$i][$r][$c] = (mt_rand() / mt_getrandmax() * 2 - 1) * $limit;
				}
				$this->biases[$i][$r] = (mt_rand() / mt_getrandmax() * 2 - 1) * 0.1;
			}
		}
	}

	public function fit(array $features, array $targets, ?float $learningRate = null, ?int $epochs = null): void
	{
		if (count($features) === 0) {
			return;
		}

		$lr = $learningRate ?? $this->learningRate;
		$maxEpochs = $epochs ?? $this->epochs;

		// Create a deterministic seed based on the data
		// Same data = same seed = same model
		$dataHash = crc32(json_encode([$features, $targets]));
		$seed = abs($dataHash) % 2147483647; // Ensure valid seed range
		
		// Initialize weights with deterministic seed
		$this->initializeWeights($seed);

		// Normalize features and targets
		$normalized = $this->normalizeData($features, $targets);
		$normFeatures = $normalized['features'];
		$normTargets = $normalized['targets'];
		$this->featureMean = $normalized['featureMean'];
		$this->featureStd = $normalized['featureStd'];
		$this->targetMean = $normalized['targetMean'];
		$this->targetStd = $normalized['targetStd'];

		$n = count($normFeatures);
		
		// Training loop with learning rate decay for better convergence
		for ($epoch = 0; $epoch < $maxEpochs; $epoch++) {
			$totalError = 0.0;
			
			// Learning rate decay: reduce learning rate gradually for better convergence
			$currentLr = $lr * (1.0 / (1.0 + 0.001 * $epoch));
			
			// Deterministic shuffle based on epoch and seed
			$indices = range(0, $n - 1);
			$this->deterministicShuffle($indices, $seed + $epoch);
			
			foreach ($indices as $idx) {
				$x = $normFeatures[$idx];
				$y = $normTargets[$idx];
				
				// Forward pass
				$activations = $this->forward($x);
				$output = $activations[count($activations) - 1][0];
				
				// Calculate error
				$error = $output - $y;
				$totalError += $error * $error;
				
				// Backward pass (backpropagation) with decayed learning rate
				$this->backward($activations, $x, $error, $currentLr);
			}
			
			// Early stopping if error is very small
			if ($totalError / $n < 1e-6) {
				break;
			}
		}
	}

	/**
	 * Deterministic shuffle using Fisher-Yates algorithm with seeded random
	 */
	private function deterministicShuffle(array &$array, int $seed): void
	{
		mt_srand($seed);
		$n = count($array);
		for ($i = $n - 1; $i > 0; $i--) {
			$j = mt_rand(0, $i);
			$temp = $array[$i];
			$array[$i] = $array[$j];
			$array[$j] = $temp;
		}
	}

	public function predict(array $feature): float
	{
		// Normalize input
		$normalized = [];
		for ($i = 0; $i < count($feature); $i++) {
			$mean = $this->featureMean[$i] ?? 0.0;
			$std = $this->featureStd[$i] ?? 1.0;
			if ($std < 1e-8) $std = 1.0;
			$normalized[] = ($feature[$i] - $mean) / $std;
		}
		
		// Forward pass
		$activations = $this->forward($normalized);
		$output = $activations[count($activations) - 1][0];
		
		// Denormalize output
		$mean = $this->targetMean ?? 0.0;
		$std = $this->targetStd ?? 1.0;
		if ($std < 1e-8) $std = 1.0;
		
		return $output * $std + $mean;
	}

	private function forward(array $input): array
	{
		$activations = [$input];
		$current = $input;
		
		for ($layer = 0; $layer < count($this->weights); $layer++) {
			$next = [];
			for ($i = 0; $i < count($this->weights[$layer]); $i++) {
				$sum = $this->biases[$layer][$i];
				for ($j = 0; $j < count($current); $j++) {
					$sum += $this->weights[$layer][$i][$j] * $current[$j];
				}
				$next[] = $this->sigmoid($sum);
			}
			$activations[] = $next;
			$current = $next;
		}
		
		return $activations;
	}

	private function backward(array $activations, array $input, float $error, float $lr): void
	{
		$numLayers = count($this->weights);
		$deltas = [];
		
		// Output layer delta
		$output = $activations[count($activations) - 1][0];
		$deltas[$numLayers - 1] = [$error * $this->sigmoidDerivative($output)];
		
		// Hidden layers deltas (backpropagation)
		for ($layer = $numLayers - 2; $layer >= 0; $layer--) {
			$deltas[$layer] = [];
			$currentActivation = $activations[$layer + 1];
			
			for ($i = 0; $i < count($this->weights[$layer]); $i++) {
				$sum = 0.0;
				for ($j = 0; $j < count($this->weights[$layer + 1]); $j++) {
					$sum += $this->weights[$layer + 1][$j][$i] * $deltas[$layer + 1][$j];
				}
				$deltas[$layer][$i] = $sum * $this->sigmoidDerivative($currentActivation[$i]);
			}
		}
		
		// Update weights and biases
		for ($layer = 0; $layer < $numLayers; $layer++) {
			$prevActivation = $layer === 0 ? $input : $activations[$layer];
			
			for ($i = 0; $i < count($this->weights[$layer]); $i++) {
				$delta = $deltas[$layer][$i];
				
				// Update bias
				$this->biases[$layer][$i] -= $lr * $delta;
				
				// Update weights
				for ($j = 0; $j < count($prevActivation); $j++) {
					$this->weights[$layer][$i][$j] -= $lr * $delta * $prevActivation[$j];
				}
			}
		}
	}

	private function sigmoid(float $x): float
	{
		// Clamp to prevent overflow
		$x = max(-500, min(500, $x));
		return 1.0 / (1.0 + exp(-$x));
	}

	private function sigmoidDerivative(float $x): float
	{
		$s = $this->sigmoid($x);
		return $s * (1.0 - $s);
	}

	private function normalizeData(array $features, array $targets): array
	{
		$n = count($features);
		$m = count($features[0]);
		
		// Calculate means and stds for features
		$featureMean = [];
		$featureStd = [];
		
		for ($j = 0; $j < $m; $j++) {
			$sum = 0.0;
			$sumSq = 0.0;
			for ($i = 0; $i < $n; $i++) {
				$val = (float)$features[$i][$j];
				$sum += $val;
				$sumSq += $val * $val;
			}
			$featureMean[$j] = $sum / $n;
			$variance = ($sumSq / $n) - ($featureMean[$j] * $featureMean[$j]);
			$featureStd[$j] = sqrt(max($variance, 1e-8));
		}
		
		// Calculate mean and std for targets
		$targetSum = array_sum(array_map('floatval', $targets));
		$targetMean = $targetSum / $n;
		$targetSumSq = array_sum(array_map(fn($t) => $t * $t, array_map('floatval', $targets)));
		$targetVariance = ($targetSumSq / $n) - ($targetMean * $targetMean);
		$targetStd = sqrt(max($targetVariance, 1e-8));
		
		// Normalize
		$normFeatures = [];
		$normTargets = [];
		
		for ($i = 0; $i < $n; $i++) {
			$normRow = [];
			for ($j = 0; $j < $m; $j++) {
				$val = (float)$features[$i][$j];
				$normRow[] = ($val - $featureMean[$j]) / $featureStd[$j];
			}
			$normFeatures[] = $normRow;
			
			$val = (float)$targets[$i];
			$normTargets[] = ($val - $targetMean) / $targetStd;
		}
		
		return [
			'features' => $normFeatures,
			'targets' => $normTargets,
			'featureMean' => $featureMean,
			'featureStd' => $featureStd,
			'targetMean' => $targetMean,
			'targetStd' => $targetStd,
		];
	}
}

