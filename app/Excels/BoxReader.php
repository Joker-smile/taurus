<?php

namespace App\Excels;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class BoxReader
{
    public function handle($file)
    {
        $reader = $this->type($file);
        $path = $file->path();
        $reader->setShouldFormatDates(true);
        $reader->setShouldPreserveEmptyRows(true);
        $reader->open($path);

        foreach ($reader->getSheetIterator() as $sheet) {
            break;
        }

        return $sheet->getRowIterator();

    }

    protected function type($file)
    {
        $type = strtolower($file->getClientOriginalExtension());

        switch ($type) {
            case 'xlsx':
                $result = ReaderEntityFactory::createXLSXReader();
                break;

            default:
                $result = ReaderEntityFactory::createCSVReader();
                break;
        }

        return $result;
    }
}
