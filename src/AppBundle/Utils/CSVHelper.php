<?php

// src/AppBundle/Utils/CSVHelper.php

namespace AppBundle\Utils;

class CSVHelper
{
    public static $eof_string = 'END OF FILE';
    private $headers = [];
    private $data = [];
    private $fileName = null; //Includes Extension
    private $fileExtension = null;
    private $filePath = null;
    private $CSVFile = null;
    private $headerWhiteList = [];
    private $headerRowIndex = 0;
    public function __construct()
    {
    }

    public function processFile($filePath, $fileName)
    {
        $this->setfileName($fileName);
        $this->setfilePath($filePath);

        $counter = 0;
        $thisRow;

        $this->setCSVFile(fopen($this->getFilePath().$this->getFileName(), 'r'));

        while (!feof($this->getCSVFile())) {
            $thisRow = fgetcsv($this->getCSVFile());
            if (!empty($thisRow)) {
                $thisRowAsObjects = [];
                if ($counter == $this->getHeaderRowIndex()) {
                    $this->setHeaders($thisRow);
                } elseif ($counter > $this->getHeaderRowIndex()) {
                    //While adding, we check for end-of-file
                    if (!$this->addRow($thisRow)) {
                        break;
                    }
                }
                ++$counter;
            }
        }

        fclose($this->getCSVFile());
    }

    public function unlink()
    {
        unlink($this->getFilePath().$this->getFileName());
    }

    public function setCSVFile($CSVFile)
    {
        $this->CSVFile = $CSVFile;
    }

    public function getCSVFile()
    {
        return $this->CSVFile;
    }

    public function setHeaderWhiteList($headerWhiteList)
    {
        $this->headerWhiteList = $headerWhiteList;
    }

    public function getHeaderWhiteList($headerWhiteList)
    {
        return $this->headerWhiteList;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setHeaders($headers)
    {
        $this->headers = $this->cleanHeaders($headers);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaderRowIndex($headerRowIndex)
    {
        $this->headerRowIndex = $headerRowIndex;
    }

    public function getHeaderRowIndex()
    {
        return $this->headerRowIndex;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function addRow($rowData)
    {
        $theObject = [];
        $row = $this->cleanRow($rowData);
        foreach ($this->getHeaders() as $key => $value) {
            $theObject[$value] = $row[$key];
            if (strcmp($row[$key], self::$eof_string) == 0) {
                return false;
            }
        }
        array_push($this->data, $theObject);

        return true;
    }

    public function cleanRow($rowData)
    {
        foreach ($rowData as $key => $value) {
            $rowData[$key] = $this->cleanDataString($value);
        }

        return $rowData;
    }

    public function cleanHeaders($headers)
    {
        foreach ($headers as $key => $value) {
            $headers[$key] = $this->cleanHeaderString($value);
        }

        return $headers;
    }

    public function addHeader($header)
    {
        array_push($this->header, $this->cleanHeaders($header));
    }

    public function getRow($index)
    {
        return $this->data[$index];
    }

    /**
     * @return array of objects
     *
     * @param array of strings $base
     * @param array of objects $compare
     * @param string           $method
     */
    public function validateHeaders($compare)
    {
        $check = true;
        $makeStringArray = [];
        //first we are turning the array of objects into array of strings
        $makeStringArray = $this->createStringArray($compare);

        foreach ($this->getHeaders() as $key => $value) {
            $loopCheck = false;
            foreach ($makeStringArray as $key2 => $value2) {
                if (strcmp($value, $value2) == 0) {
                    $loopCheck = true;
                }
            }
            if (!$loopCheck) {
                $check = false;
            }
        }

        return $check;
    }

    public function createStringArray($in)
    {
        $makeStringArray = [];

        //first we are turning the array of objects into array of strings
        foreach ($in as $key => $value) {
            array_push($makeStringArray, $value);
        }

        return $makeStringArray;
    }

    public function cleanHeaderString($string)
    {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with underscores.
        $string = preg_replace('/[^A-Za-z0-9\_]/', '', $string); // Removes special chars.
        $string = trim($string);

        return strtolower($string);
    }

    public function cleanDataString($string)
    {
        $string = preg_replace('/[^A-Za-z0-9@\/_ -.]/', '', $string); // Removes special chars.
        $string = str_replace("'", '', $string); // Removing Quotes
        $string = trim($string);

        return $string;
    }
    public function cleanAmountString($string)
    {
        $string = preg_replace('/[^0-9.]*/', '', $string); // Removes special chars.
        $string = trim($string);

        return floatval($string);
    }

    public function cleanClassroomNames()
    {
        $thisData = $this->getData();
        foreach ($thisData as $rowIndex => $rowData) {
            foreach ($rowData as $key => $value) {
                if (strcmp($key, 'classrooms_name') == 0) {
                    $teacherNameString = substr(trim($value), strpos(trim($value), ' - ') + 3, strlen(trim($value)));
                    $thisData[$rowIndex][$key] = $teacherNameString;
                }
            }
        }

        $this->setData($thisData);
    }

    public function cleanAmounts()
    {
        $thisData = $this->getData();
        foreach ($thisData as $rowIndex => $rowData) {
            foreach ($rowData as $key => $value) {
                if (strcmp($key, 'amount') == 0) {
                    $thisData[$rowIndex][$key] = $this->cleanAmountString($value);
                }
            }
        }

        $this->setData($thisData);
    }

    /**
     * This is really only applicable to "CauseVox" donation files.
     */
    public function getGradefromClassroomName()
    {
        $thisData = $this->getData();
        foreach ($thisData as $rowIndex => $rowData) {
            foreach ($rowData as $key => $value) {
                if (strcmp($key, 'classrooms_name') == 0) {
                    $gradeString = substr(trim($value), 0, strpos(trim($value), ' - '));
                    $thisData[$rowIndex]['grade'] = $gradeString;
                }
            }
        }

        $this->setData($thisData);
    }

    public function getFirstNameFromFullName()
    {
        $thisData = $this->getData();
        foreach ($thisData as $rowIndex => $rowData) {
            foreach ($rowData as $key => $value) {
                if (strcmp($key, 'students_name') == 0) {
                  //If there is a "Space", We assume there is a last name in there
                  if(strpos(trim($value), ' ')){
                    $firstname = ucfirst(substr($value, 0, strpos($value, ' '))); //Getting First name
                    $thisData[$rowIndex]['students_first_name'] = $firstname;

                    $lastname = ucfirst(substr($value, strpos($value, ' ')+1,strlen($value))); //Getting Last Name
                    $lastinitial = substr($lastname,0,1).'.';
                    //ucfirst
                    //TURNING David Larrimore into "David L."
                    $thisData[$rowIndex]['students_name_with_initial'] = $firstname.' '.$lastinitial;
                  }else{
                    $thisData[$rowIndex]['students_first_name'] = $value;
                    $thisData[$rowIndex]['students_name_with_initial'] = $value;
                  }
                }
            }
        }

        $this->setData($thisData);
    }
}
