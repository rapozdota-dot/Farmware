<?php

namespace App\Services;

class LinearRegression
{
	private array $weights = [];
	private float $bias = 0.0;

	public function fit(array $features, array $targets, float $ridge = 1e-6): void
	{
		if (count($features) === 0) {
			$this->weights = [];
			$this->bias = 0.0;
			return;
		}

		$n = count($features);
		$m = count($features[0]);

		$X = [];
		for ($i = 0; $i < $n; $i++) {
			$row = [1.0];
			for ($j = 0; $j < $m; $j++) {
				$row[] = (float)$features[$i][$j];
			}
			$X[] = $row;
		}

		$XT = self::transpose($X);
		$XTX = self::matMul($XT, $X);

		for ($i = 1; $i < count($XTX); $i++) {
			$XTX[$i][$i] += $ridge;
		}

		$XTy = self::matVecMul($XT, array_map('floatval', $targets));
		$beta = self::solve($XTX, $XTy);

		$this->bias = $beta[0] ?? 0.0;
		$this->weights = [];
		for ($j = 1; $j < count($beta); $j++) {
			$this->weights[] = $beta[$j];
		}
	}

	public function predict(array $feature): float
	{
		$y = $this->bias;
		for ($j = 0; $j < count($this->weights); $j++) {
			$y += $this->weights[$j] * (float)($feature[$j] ?? 0.0);
		}
		return $y;
	}

	private static function transpose(array $A): array
	{
		$rows = count($A);
		$cols = count($A[0]);
		$T = array_fill(0, $cols, array_fill(0, $rows, 0.0));
		for ($i = 0; $i < $rows; $i++) {
			for ($j = 0; $j < $cols; $j++) {
				$T[$j][$i] = (float)$A[$i][$j];
			}
		}
		return $T;
	}

	private static function matMul(array $A, array $B): array
	{
		$r = count($A);
		$c = count($B[0]);
		$kmax = count($B);
		$C = array_fill(0, $r, array_fill(0, $c, 0.0));
		for ($i = 0; $i < $r; $i++) {
			for ($k = 0; $k < $kmax; $k++) {
				$Aik = (float)$A[$i][$k];
				for ($j = 0; $j < $c; $j++) {
					$C[$i][$j] += $Aik * (float)$B[$k][$j];
				}
			}
		}
		return $C;
	}

	private static function matVecMul(array $A, array $x): array
	{
		$r = count($A);
		$c = count($A[0]);
		$y = array_fill(0, $r, 0.0);
		for ($i = 0; $i < $r; $i++) {
			$sum = 0.0;
			for ($j = 0; $j < $c; $j++) {
				$sum += (float)$A[$i][$j] * (float)$x[$j];
			}
			$y[$i] = $sum;
		}
		return $y;
	}

	private static function solve(array $A, array $b): array
	{
		$n = count($A);
		$M = $A;
		$y = $b;
		for ($i = 0; $i < $n; $i++) {
			$pivot = $M[$i][$i];
			if (abs($pivot) < 1e-12) {
				for ($k = $i + 1; $k < $n; $k++) {
					if (abs($M[$k][$i]) > abs($pivot)) {
						$tmp = $M[$i];
						$M[$i] = $M[$k];
						$M[$k] = $tmp;
						$tmpy = $y[$i];
						$y[$i] = $y[$k];
						$y[$k] = $tmpy;
						$pivot = $M[$i][$i];
						break;
					}
				}
			}
			if (abs($pivot) < 1e-12) {
				continue;
			}
			for ($j = $i; $j < $n; $j++) {
				$M[$i][$j] /= $pivot;
			}
			$y[$i] /= $pivot;
			for ($k = 0; $k < $n; $k++) {
				if ($k === $i) continue;
				$factor = $M[$k][$i];
				for ($j = $i; $j < $n; $j++) {
					$M[$k][$j] -= $factor * $M[$i][$j];
				}
				$y[$k] -= $factor * $y[$i];
			}
		}
		return $y;
	}
}


