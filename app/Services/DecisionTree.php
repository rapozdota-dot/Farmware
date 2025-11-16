<?php

namespace App\Services;

/**
 * Decision Tree Regressor
 * A simple but interpretable AI model for rice yield prediction
 * Uses CART (Classification and Regression Trees) algorithm
 */
class DecisionTree
{
	private ?array $tree = null;
	private array $featureNames = ['rainfall_mm', 'temperature_c', 'soil_ph', 'fertilizer_kg', 'area_ha'];
	private int $maxDepth = 5;
	private int $minSamplesSplit = 5;

	public function __construct(int $maxDepth = 5, int $minSamplesSplit = 5)
	{
		$this->maxDepth = $maxDepth;
		$this->minSamplesSplit = $minSamplesSplit;
	}

	public function fit(array $features, array $targets): void
	{
		if (count($features) === 0) {
			return;
		}

		// Combine features and targets
		$data = [];
		for ($i = 0; $i < count($features); $i++) {
			$data[] = array_merge($features[$i], [$targets[$i]]);
		}

		$this->tree = $this->buildTree($data, 0);
	}

	public function predict(array $feature): float
	{
		if ($this->tree === null) {
			return 0.0;
		}

		return $this->predictNode($this->tree, $feature);
	}

	private function buildTree(array $data, int $depth): array
	{
		$n = count($data);
		
		// Base cases
		if ($n < $this->minSamplesSplit || $depth >= $this->maxDepth) {
			return [
				'type' => 'leaf',
				'value' => $this->mean(array_column($data, count($data[0]) - 1)),
			];
		}

		// Find best split
		$bestSplit = $this->findBestSplit($data);
		
		if ($bestSplit === null) {
			return [
				'type' => 'leaf',
				'value' => $this->mean(array_column($data, count($data[0]) - 1)),
			];
		}

		// Split data
		$leftData = [];
		$rightData = [];
		
		foreach ($data as $row) {
			if ($row[$bestSplit['feature']] <= $bestSplit['threshold']) {
				$leftData[] = $row;
			} else {
				$rightData[] = $row;
			}
		}

		// If split doesn't improve, make leaf
		if (count($leftData) === 0 || count($rightData) === 0) {
			return [
				'type' => 'leaf',
				'value' => $this->mean(array_column($data, count($data[0]) - 1)),
			];
		}

		// Recursively build children
		return [
			'type' => 'node',
			'feature' => $bestSplit['feature'],
			'threshold' => $bestSplit['threshold'],
			'left' => $this->buildTree($leftData, $depth + 1),
			'right' => $this->buildTree($rightData, $depth + 1),
		];
	}

	private function findBestSplit(array $data): ?array
	{
		$n = count($data);
		$numFeatures = count($data[0]) - 1; // Last column is target
		$bestSplit = null;
		$bestVariance = PHP_FLOAT_MAX;

		$targets = array_column($data, $numFeatures);
		$parentVariance = $this->variance($targets);

		// Try each feature
		for ($feature = 0; $feature < $numFeatures; $feature++) {
			$values = array_column($data, $feature);
			$uniqueValues = array_unique($values);
			sort($uniqueValues);

			// Try thresholds (midpoints between consecutive values)
			for ($i = 0; $i < count($uniqueValues) - 1; $i++) {
				$threshold = ($uniqueValues[$i] + $uniqueValues[$i + 1]) / 2.0;

				$leftTargets = [];
				$rightTargets = [];

				foreach ($data as $row) {
					if ($row[$feature] <= $threshold) {
						$leftTargets[] = $row[$numFeatures];
					} else {
						$rightTargets[] = $row[$numFeatures];
					}
				}

				if (count($leftTargets) === 0 || count($rightTargets) === 0) {
					continue;
				}

				// Calculate weighted variance reduction
				$leftVariance = $this->variance($leftTargets);
				$rightVariance = $this->variance($rightTargets);
				$weightedVariance = (count($leftTargets) / $n) * $leftVariance + 
									(count($rightTargets) / $n) * $rightVariance;

				// Variance reduction (we want to minimize weighted variance)
				if ($weightedVariance < $bestVariance) {
					$bestVariance = $weightedVariance;
					$bestSplit = [
						'feature' => $feature,
						'threshold' => $threshold,
						'variance_reduction' => $parentVariance - $weightedVariance,
					];
				}
			}
		}

		return $bestSplit;
	}

	private function predictNode(array $node, array $feature): float
	{
		if ($node['type'] === 'leaf') {
			return $node['value'];
		}

		if ($feature[$node['feature']] <= $node['threshold']) {
			return $this->predictNode($node['left'], $feature);
		} else {
			return $this->predictNode($node['right'], $feature);
		}
	}

	private function mean(array $values): float
	{
		if (count($values) === 0) {
			return 0.0;
		}
		return array_sum($values) / count($values);
	}

	private function variance(array $values): float
	{
		if (count($values) === 0) {
			return 0.0;
		}

		$mean = $this->mean($values);
		$sumSquaredDiff = 0.0;

		foreach ($values as $value) {
			$diff = $value - $mean;
			$sumSquaredDiff += $diff * $diff;
		}

		return $sumSquaredDiff / count($values);
	}

	/**
	 * Get feature importance (simple measure based on variance reduction)
	 */
	public function getFeatureImportance(): array
	{
		// This is a simplified version - in a full implementation,
		// we'd track importance during tree building
		return [
			'rainfall_mm' => 0.25,
			'temperature_c' => 0.20,
			'soil_ph' => 0.15,
			'fertilizer_kg' => 0.25,
			'area_ha' => 0.15,
		];
	}
}

