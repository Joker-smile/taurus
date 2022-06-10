<?php

namespace App\Excels;

use Illuminate\Support\Facades\File;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class BoxWriter
{
    /**
     * @var \Box\Spout\Writer\WriterInterface
     */
    protected $writer;

    /**
     * @var string
     */
    protected $path;

    /**
     * 初始化一个表格
     */
    public function start(
        string $filename,
        array $header,
        string $directory = 'exports',
        string $ext = 'xlsx',
        string $delimiter = ','
    ): void
    {
        $this->init($ext, $delimiter);

        $this->path = storage_path($directory . "/{$filename}.{$ext}");

        $this->checkDirectory($directory);

        $this->writer->openToFile($this->path);

        $this->addRows($header);
    }

    /**
     * 添加一行数据.
     */
    public function addRow(array $row): void
    {
        $row = WriterEntityFactory::createRowFromArray($row);
        $this->writer->addRow($row);
    }

    /**
     * 添加多行数据.
     */
    public function addRows(array $rows): void
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    /**
     * 保存表格
     */
    public function save(): string
    {
        $this->writer->close();

        return $this->path;
    }

    private function checkDirectory(string $directory): void
    {
        $path = storage_path($directory);

        if (File::exists($path)) {
            return;
        }

        File::makeDirectory($path);
    }

    /**
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterAlreadyOpenedException
     */
    private function init(string $ext, string $delimiter = ','): void
    {
        if ('csv' == $ext) {
            $this->writer = WriterEntityFactory::createCSVWriter();
            $this->writer->setFieldDelimiter($delimiter);
        } else {
            $this->writer = WriterEntityFactory::createXLSXWriter();
            $this->writer->setShouldUseInlineStrings(true);
        }
    }
}
