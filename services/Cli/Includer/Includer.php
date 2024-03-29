<?php

namespace Adebipe\Cli\Includer;

/**
 * Get all files from a directory and his subdirectories
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Includer implements IncluderInterface
{
    /**
     * Find all files in a directory and follow the runtime
     *
     * @param string $path The path of the directory
     *
     * @return array
     */
    private function _readPath($path): array
    {
        $result = [
            "atStart" => [],
            "middle" => [],
            "atEnd" => []
        ];
        if (!is_dir($path)) { // If it's a file
            $result["middle"][] = $path;
            return $result;
        }

        $in_dir = scandir($path);
        if ($in_dir === false) {
            throw new \Exception("Can't read the directory: " . $path);
        }
        if (!in_array("runtime", $in_dir)) { // If it's a directory without runtime
            foreach ($in_dir as $item) {
                if ($item === "." || $item === "..") {
                    continue;
                }
                $loaded = $this->_readPath($path . "/" . $item);
                $result["atStart"] = array_merge($result["atStart"], $loaded["atStart"]);
                $result["middle"] = array_merge($result["middle"], $loaded["middle"]);
                $result["atEnd"] = array_merge($result["atEnd"], $loaded["atEnd"]);
            }
            return $result;
        }
        $loaded = $this->_decodeDirRuntime($path); // If it's a directory with runtime
        $result["atStart"] = array_merge($result["atStart"], $loaded["atStart"]);
        $result["middle"] = array_merge($result["middle"], $loaded["middle"]);
        $result["atEnd"] = array_merge($result["atEnd"], $loaded["atEnd"]);
        return $result;
    }

    /**
     * Find all files in a directory with the runtime
     *
     * @param string $path The path of the directory
     *
     * @return array
     */
    private function _decodeDirRuntime($path): array
    {
        $decoded_runtime = [
            "atStart" => [],
            "middle" => [],
            "atEnd" => []
        ];
        $in_dir = scandir($path);

        $already_process = [".", "..", "runtime"];
        $runtime = fopen($path . '/runtime', 'r');
        if ($runtime === false) {
            throw new \Exception("Can't read the runtime file: " . $path . '/runtime');
        }
        $data = fread($runtime, filesize($path . '/runtime') ?: 0);
        fclose($runtime);
        $runtime_data = explode(PHP_EOL, $data ?: "");
        foreach ($runtime_data as $item) {
            if (strpos($item, '@Not ') !== false) {
                $filename = str_replace('@Not ', '', $item);
                $already_process[] = $filename;
                continue;
            }
            if (strpos($item, '@Last ') !== false) {
                $filename = str_replace('@Last ', '', $item);
                $new_file = $path . "/" . $filename;
                $already_process[] = $filename;
                $new_file = $this->_readPath($new_file);
                $decoded_runtime["atEnd"] = array_merge($decoded_runtime["atEnd"], $new_file["atStart"]);
                $decoded_runtime["atEnd"] = array_merge($decoded_runtime["atEnd"], $new_file["middle"]);
                $decoded_runtime["atEnd"] = array_merge($decoded_runtime["atEnd"], $new_file["atEnd"]);
                continue;
            }
            $already_process[] = $item;
            $new_file = $path . '/' . $item;
            $new_file = $this->_readPath($new_file);
            $decoded_runtime["atStart"] = array_merge($decoded_runtime["atStart"], $new_file["atStart"]);
            $decoded_runtime["atStart"] = array_merge($decoded_runtime["atStart"], $new_file["middle"]);
            $decoded_runtime["atStart"] = array_merge($decoded_runtime["atStart"], $new_file["atEnd"]);
        }

        if (!is_array($in_dir)) {
            throw new \Exception("Can't read the directory: " . $path);
        }

        foreach ($in_dir as $item) {
            if (in_array($item, $already_process)) {
                continue;
            }
            $already_process[] = $item;
            $new_file = $path . '/' . $item;
            $new_file = $this->_readPath($new_file);
            $decoded_runtime["middle"] = array_merge($decoded_runtime["middle"], $new_file["atStart"]);
            $decoded_runtime["middle"] = array_merge($decoded_runtime["middle"], $new_file["middle"]);
            $decoded_runtime["middle"] = array_merge($decoded_runtime["middle"], $new_file["atEnd"]);
        }
        return $decoded_runtime;
    }

    /**
     * Find all files in a directory and his subdirectories
     *
     * @param string $path The path of the directory
     *
     * @return array<string>
     */
    public function findAllFile($path): array
    {
        $result = $this->_readPath($path);
        $result = array_merge($result["atStart"], $result["middle"], $result["atEnd"]);
        $result = array_unique($result);
        $result = array_filter(
            $result,
            function ($item) {
                return !is_dir($item) && strpos($item, '.php') === strlen($item) - 4;
            }
        );
        return $result;
    }

    /**
     * Include all files in a directory and his subdirectories
     *
     * @param string $path The path of the directory
     *
     * @return array<string>
     */
    public function includeAllFile($path): array
    {
        $all_file = $this->findAllFile($path);
        $initialized_class = [];
        foreach ($all_file as $file) {
            $initialized_class = array_merge($initialized_class, $this->includeFile($file));
        }
        return $initialized_class;
    }

    /**
     * Include a file
     *
     * @param string $path The path of the file
     *
     * @return array<string> The classes declared in the file
     */
    public function includeFile($path): array
    {
        if (!file_exists($path)) {
            throw new \Exception("File not found: " . $path);
        }
        $all_class = get_declared_classes();
        include_once $path;
        $new_class = array_diff(get_declared_classes(), $all_class);
        $new_class = array_values($new_class);
        return $new_class;
    }
}
