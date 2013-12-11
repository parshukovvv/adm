<?php

namespace Nimax;

define('EOL', (php_sapi_name() == 'cli' ? PHP_EOL : '<br>'));

/**
 * Class Anonymous Santa Claus
 *
 * @package     Nimax
 * @author      Vladimir Parshukov <vladimir.p@nimax.ru>
 * @version     1.0
 * @copyright   (c) 2013 Nimax
 * @license     GNU Public License http://opensource.org/licenses/gpl-license.php
 *
 */
class ADM {

    /**
     * @var string Current directory
     */
    private $curDir = '';
    /**
     * @var string Path to input file
     */
    private $inputFile = '';
    /**
     * @var array Input data
     */
    private $inputArray = array();
    /**
     * @var string Path to output file
     */
    private $outputFile = '';
    /**
     * @var array Output data
     */
    private $outputArray = array();

    /**
     * @var array Default config
     */
    private $config = array(
        // Stack of messages
        'mailStack' => 5,
        // Delay before sending next stack of messages
        'mailSleep' => 10,
        // Email default sender
        'mailFrom' => 'test@test.tt',
        // Subject of the message
        'mailSubject' => '',
        // Message
        'mailMessage' => ''
    );

    /**
     * Execute ADM script
     *
     * @param array $config
     */
    public static function execute($config = array())
    {
        try {
            $adm = new self($config);
            $adm->check();
            if(!$adm->checkOutput())
            {
                $adm->readInput();
                $adm->linksGeneration();
                $adm->saveOutput();
            }
            $adm->send();
        }
        catch(\Exception $e){
            echo 'ERROR: '.$e->getMessage().EOL;
        }
        echo 'DONE!';
    }

    /**
     * Construct, init base variables
     */
    function __construct($config = array())
    {
        $this->config = array_merge($this->config, $config);

        $this->curDir = __DIR__.DIRECTORY_SEPARATOR;
        $this->inputFile = $this->curDir.'input.php';
        $this->outputFile = $this->curDir.'output.php';

        echo 'Welcome to the anonymous Santa Claus.'.EOL;
        echo 'Have a good holiday!'.EOL;
        echo '---------------------------'.EOL;
    }

    /**
     * Check requirements
     *
     * @throws \Exception
     */
    public function check()
    {
        $errors = array();
        if(version_compare(PHP_VERSION, '5.3.0') < 0)
            $errors[] = '- Requires PHP >= 5.3.0';
        if(!is_readable($this->curDir))
            $errors[] = '- The directory '.$this->curDir.' is not readable';
        if(!is_writable($this->curDir))
            $errors[] = '- The directory '.$this->curDir.' is not writable';
        if(!function_exists('json_encode'))
            $errors[] = '- Json_encode function is not supported';
        if(!function_exists('json_decode'))
            $errors[] = '- Json_decode function is not supported';

        if(!empty($errors))
            throw new \Exception('The script can not be run, correct the following requirements:'.EOL.implode(EOL, $errors));
    }

    /**
     * Read input data
     *
     * @throws \Exception
     */
    public function readInput()
    {
        if(!is_readable($this->inputFile))
            throw new \Exception('File input.php is not found or is not readable in the directory '.$this->curDir);

        $this->inputArray = require $this->inputFile;

        if(!is_array($this->inputArray) || empty($this->inputArray))
            throw new \Exception('The input array is empty');
    }

    /**
     * Generate gift links
     */
    public function linksGeneration()
    {
        $cntInputArray = count($this->inputArray);
        echo 'Count of people from input data: '.$cntInputArray.EOL;

        $firstPeople = $this->inputArray[0];
        while($cntInputArray > 0)
        {
            $curPeople = &$this->inputArray[0];
            $randNum = rand(1,--$cntInputArray);
            $whomHePresents = (isset($this->inputArray[$randNum]) ? $this->inputArray[$randNum] : $firstPeople);
            $this->outputArray[] = array(
                'who' => $curPeople,
                'whom' => $whomHePresents
            );
            $curPeople = $whomHePresents;
            unset($this->inputArray[$randNum]);
            $this->inputArray = array_values($this->inputArray);
        }
        unset($this->inputArray[0]);

        echo 'Anonymous Santa Claus finished links generation'.EOL;
    }

    /**
     * Save output data
     *
     * @throws \Exception
     */
    public function saveOutput()
    {
        $encodeOutputArray = json_encode($this->outputArray);
        if($encodeOutputArray === FALSE)
            throw new \Exception('Failed to convert data in json string');

        $saveOutput = file_put_contents($this->outputFile, $encodeOutputArray);
        if($saveOutput === FALSE)
            throw new \Exception('Failed to save data to a file output.php');
    }

    /**
     * Check output data
     *
     * @return bool TRUE if file is isset and readable, FALSE if not
     * @throws \Exception
     */
    public function checkOutput()
    {
        if(is_readable($this->outputFile))
        {
            $outputData = file_get_contents($this->outputFile);
            if($outputData === FALSE)
                throw new \Exception('Failed to read data from a file output.php');

            $decodeOutputData = json_decode($outputData, TRUE);
            if($decodeOutputData === FALSE || !is_array($decodeOutputData))
                throw new \Exception('Unable to decode the data from the file output.php');

            $this->outputArray = $decodeOutputData;

            echo 'Re-read data from the file output.php'.EOL;

            return TRUE;
        }
        return FALSE;
    }

    /**
     * Read output data and send messages
     *
     * @throws \Exception
     */
    public function send()
    {
        if(!empty($this->outputArray))
        {
            $i = 0;
            $cntOutputArray = count($this->outputArray);
            foreach($this->outputArray as $k => $data)
            {
                if(!$this->mail($data['who']['email'], $data['who']['name'], $data['whom']['name']))
                    throw new \Exception('');

                unset($this->outputArray[$k]);
                $this->saveOutput();

                echo 'Message sent to the person #'.($k+1).EOL;

                if((++$i % $this->config['mailStack'] == 0) && $cntOutputArray > $i)
                    sleep($this->config['mailSleep']);
            }
        }

        if(empty($this->outputArray))
        {
            if(unlink($this->outputFile) === FALSE)
                throw new \Exception('Failed to delete file output.php, remove it manually');

            echo 'All messages sent successfully'.EOL;
        }
    }

    /**
     * Send message
     *
     * @param string $emailTo Email recipient
     * @param string $who Name of recipient
     * @param string $whom Name of the person who get the gift from recipient
     * @return bool TRUE if mail send, FALSE if not
     */
    private function mail($emailTo = '', $who = '', $whom = '')
    {
        $subject = "=?UTF-8?b?".base64_encode($this->config['mailSubject'])."?=";

        $headers   = array();
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'From: '.$this->config['mailFrom'];
        $headers[] = 'Reply-To: '.$this->config['mailFrom'];
        $headers[] = 'Subject: '.$subject;
        $headers[] = 'X-Mailer: PHP/'.phpversion();

        $message = str_replace(array('#WHO#', '#WHOM#'), array($who, $whom), $this->config['mailMessage']);

        return mail($emailTo, $subject, $message, implode("\r\n", $headers));
    }
}