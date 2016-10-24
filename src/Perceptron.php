<?php

namespace Neural;

class Perceptron
{
    protected $vectorLength;
    protected $bias;
    protected $learningRate;

    protected $weightVector;
    protected $iterations = 0;

    protected $errorSum = 0;    
    protected $output = null;

    protected $label;      


    /**
     * @param int   $vectorLength The number of input signals
     * @param float $bias         Bias factor
     * @param float $learningRate The learning rate 0 < x <= 1
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($vectorLength, $label = null, $bias = 1.0, $learningRate = .05)
    {
    	$this->label = $label;

        if ($vectorLength < 1) {
            throw new \InvalidArgumentException();
        } elseif ($learningRate <= 0 || $learningRate > 1) {
            throw new \InvalidArgumentException();
        }

        $this->vectorLength = $vectorLength;
        $this->bias = $bias;
        $this->learningRate = $learningRate;

        for ($i = 0; $i < $this->vectorLength; $i++) {            
            $this->weightVector[$i] = mt_rand()/getrandmax() * 0.2;
        } 
        $this->weightVector[] = $this->bias; //acompanhar bias
        $this->vectorLength++; //acompanhar bias
    }

    public function setLabel($l)
    {
    	$this->label = $l;
    }

    public function getLabel()
    {
    	return $this->label;
    }




    public function getOutput()
    {
        if(is_null($this->output))
        {
            throw new \RuntimeException();
        }
        else
        {
            return $this->output;
        }
    }

    /**
     * @return array
     */
    public function getWeightVector()
    {
        return $this->weightVector;
    }

    /**
     * @param array $weightVector
     *
     * @throws \InvalidArgumentException
     */
    public function setWeightVector($weightVector)
    {
        if (!is_array($weightVector)) {
            throw new \InvalidArgumentException();
        }
        $this->weightVector = $weightVector; 
        $this->weightVector[] = $this->bias;       
    }

    /**
     * @return int
     */
    public function getBias()
    {
        return $this->bias;
    }

    /**
     * @param float $bias
     *
     * @throws \InvalidArgumentException
     */
    public function setBias($bias)
    {
        if (!is_numeric($bias)) {
            throw new \InvalidArgumentException();
        }
        $this->bias = $bias;
    }

    /**
     * @return float
     */
    public function getLearningRate()
    {
        return $this->learningRate;
    }

    /**
     * @param float $learningRate
     *
     * @throws \InvalidArgumentException
     */
    public function setLearningRate($learningRate)
    {
        if (!is_numeric($learningRate) || $learningRate <= 0 || $learningRate > 1) {
            throw new \InvalidArgumentException();
        }
        $this->learningRate = $learningRate;
    }

    /**
     * @return int
     */
    public function getIterationError()
    {
        return $this->iterationError;
    }


    public function getErrorSum()
    {
    	return $this->errorSum;
    }

    /**
     * Perceptron function here.      
     * @param array $inputVector     
     * @return float value between 0 and 1, contribution of input xi to perceptron output
     * @throws \InvalidArgumentException
     */    
    public function test($inputVector)
    {
        if (!is_array($inputVector)) {
            throw new \InvalidArgumentException();
        }        
        $testResult = $this->dotProduct($this->weightVector, $inputVector);
        //$this->output = $testResult > 0 ? 1 : -1;
        $this->output = $this->sigmoid($testResult / $this->vectorLength);
        return $this->output;
    }

    /**
     * @param array $inputVector array of input signals
     * @param int $outcome 1 = true / 0 = false
     *
     * @throws \InvalidArgumentException
     */
    public function train($inputVector, $outcome)
    {
        if (!is_array($inputVector) || !is_numeric($outcome)) {
            throw new \InvalidArgumentException();
        }
      
        $inputVector[] = 1;
        $this->iterations += 1;

        $this->test($inputVector);     
        $error = $this->getCellError($outcome);        

		
		

	    if($error != $outcome){

            for ($i = 0; $i < $this->vectorLength; $i++) {
                $this->weightVector[$i] += $this->learningRate * $error * $inputVector[$i];
            
            }     
            $this->errorSum++;   
        }
    }



    public function getCellError($target)
    {

    	$err = $target - $this->output;
    	return $err;
    }


    /**
     * @param array $vector1
     * @param array $vector2
     *
     * @return number
     */
    private function dotProduct($vector1, $vector2)
    {
        return array_sum(
            array_map(
                function ($a, $b) {
                    return $a * $b;
                },
                $vector1,
                $vector2
            )
        );
    }


    private function sigmoid($t)
    {		
		return (float) 1. / (1. + pow(M_E, -$t));
	}	

}