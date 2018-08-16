<?php
if (function_exists('zip_open') && function_exists('zip_read') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    class N2ZipReader {

        public static function read($path) {
            $zip = zip_open($path);
            if (!is_resource($zip)) {
                return array();
            }
            $data = array();
            while ($entry = zip_read($zip)) {

                zip_entry_open($zip, $entry, "r");

                self::recursiveRead($data, explode('/', zip_entry_name($entry)), zip_entry_read($entry, zip_entry_filesize($entry)));

                zip_entry_close($entry);
            }

            zip_close($zip);

            return $data;
        }

        private static function recursiveRead(&$data, $parts, $content) {
            if (count($parts) == 1) {
                $data[$parts[0]] = $content;
            } else {
                if (!isset($data[$parts[0]])) {
                    $data[$parts[0]] = array();
                }
                self::recursiveRead($data[array_shift($parts)], $parts, $content);
            }
        }
    }
} else {

    class N2ZipReader {

        private $fileHandle, $file;

        public function __construct($file) {
            $this->file = $file;
        }

        public static function read($path) {
            $zip = new N2ZipReader($path);

            return $zip->extract();
        }

        function extract() {
            $extractedData = array();
            if (!$this->file || !is_file($this->file)) return false;
            $filesize = sprintf('%u', filesize($this->file));

            $this->fileHandle = fopen($this->file, 'rb');
            $fileData         = fread($this->fileHandle, $filesize);

            $EofCentralDirData = $this->_findEOFCentralDirectoryRecord($filesize);
            if (!is_array($EofCentralDirData)) return false;
            $centralDirectoryHeaderOffset = $EofCentralDirData['centraldiroffset'];
            for ($i = 0; $i < $EofCentralDirData['totalentries']; $i++) {
                rewind($this->fileHandle);
                fseek($this->fileHandle, $centralDirectoryHeaderOffset);
                $centralDirectoryData         = $this->_readCentralDirectoryData();
                $centralDirectoryHeaderOffset += 46 + $centralDirectoryData['filenamelength'] + $centralDirectoryData['extrafieldlength'] + $centralDirectoryData['commentlength'];
                if (!is_array($centralDirectoryData) || substr($centralDirectoryData['filename'], -1) == '/') continue;
                $data = $this->_readLocalFileHeaderAndData($centralDirectoryData);
                if (!$data) continue;

                $dir      = dirname($centralDirectoryData['filename']);
                $fileName = basename($centralDirectoryData['filename']);
                if ($dir != '.' && $dir != '') {
                    if (!isset($extractedData[$dir])) {
                        $extractedData[$dir] = array();
                    }
                    $extractedData[$dir][$fileName] = $data;
                } else {
                    $extractedData[$fileName] = $data;
                }
            }
            fclose($this->fileHandle);

            return $extractedData;
        }

        function _findEOFCentralDirectoryRecord($filesize) {
            fseek($this->fileHandle, $filesize - 22);
            $EofCentralDirSignature = unpack('Vsignature', fread($this->fileHandle, 4));
            if ($EofCentralDirSignature['signature'] != 0x06054b50) {
                $maxLength = 65535 + 22;
                $maxLength > $filesize && $maxLength = $filesize;
                fseek($this->fileHandle, $filesize - $maxLength);
                $searchPos = ftell($this->fileHandle);
                while ($searchPos < $filesize) {
                    fseek($this->fileHandle, $searchPos);
                    $sigData = unpack('Vsignature', fread($this->fileHandle, 4));
                    if ($sigData['signature'] == 0x06054b50) {
                        break;
                    }
                    $searchPos++;
                }
            }
            $EofCentralDirData = unpack('vdisknum/vdiskstart/vcentraldirnum/vtotalentries/Vcentraldirsize/Vcentraldiroffset/vcommentlength', fread($this->fileHandle, 18));

            return $EofCentralDirData;
        }

        function _readCentralDirectoryData() {
            $centralDirectorySignature = unpack('Vsignature', fread($this->fileHandle, 4));
            if ($centralDirectorySignature['signature'] != 0x02014b50) return false;
            $centralDirectoryData = fread($this->fileHandle, 42);
            $centralDirectoryData = unpack('vmadeversion/vextractversion/vflag/vcompressmethod/vmodtime/vmoddate/Vcrc/Vcompressedsize/Vuncompressedsize/vfilenamelength/vextrafieldlength/vcommentlength/vdiskstart/vinternal/Vexternal/Vlocalheaderoffset', $centralDirectoryData);
            $centralDirectoryData['filenamelength'] && $centralDirectoryData['filename'] = fread($this->fileHandle, $centralDirectoryData['filenamelength']);

            return $centralDirectoryData;
        }

        function _readLocalFileHeaderAndData($centralDirectoryData) {
            fseek($this->fileHandle, $centralDirectoryData['localheaderoffset']);
            $localFileHeaderSignature = unpack('Vsignature', fread($this->fileHandle, 4));
            if ($localFileHeaderSignature['signature'] != 0x04034b50) return false;
            $localFileHeaderData = fread($this->fileHandle, 26);
            $localFileHeaderData = unpack('vextractversion/vflag/vcompressmethod/vmodtime/vmoddate/Vcrc/Vcompressedsize/Vuncompressedsize/vfilenamelength/vextrafieldlength', $localFileHeaderData);
            $localFileHeaderData['filenamelength'] && $localFileHeaderData['filename'] = fread($this->fileHandle, $localFileHeaderData['filenamelength']);
            if (!$this->_checkLocalFileHeaderAndCentralDir($localFileHeaderData, $centralDirectoryData)) return false;

            if ($localFileHeaderData['flag'] & 1) return false;
            $compressedData = fread($this->fileHandle, $localFileHeaderData['compressedsize']);
            $data           = $this->_unCompressData($compressedData, $localFileHeaderData['compressmethod']);

            if (crc32($data) != $localFileHeaderData['crc'] || strlen($data) != $localFileHeaderData['uncompressedsize']) return false;

            return $data;
        }

        function _unCompressData($data, $compressMethod) {
            if (!$compressMethod) return $data;
            switch ($compressMethod) {
                case 8 :
                    $data = gzinflate($data);
                    break;
                default :
                    return false;
                    break;
            }

            return $data;
        }

        function _checkLocalFileHeaderAndCentralDir($localFileHeaderData, $centralDirectoryData) {
            return true;
        }
    }
}