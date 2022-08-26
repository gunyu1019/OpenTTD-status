<?php

class SystemInformation {
    private $operationSystem;

    public function __construct($operationSystem = PHP_OS) {
        $this->operationSystem=$operationSystem;
    }

    public function cpu_usage() {
        if ($this->operationSystem == "Linux") {
            return sys_getloadavg();
        } else {
            return "Unknown";
        }
    }

    public function available_memory_usage() {
        if ($this->operationSystem == "Linux") {
            $free = shell_exec('free');
            $free = trim($free);
            $free_arr = explode("\n", $free);
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);
            return $mem[2];
        } elseif ($this->operationSystem == "WINNT") {
            $available_memory_command = shell_exec('wmic OS get FreePhysicalMemory');
            $available_memory_array = explode("\n", $available_memory_command);
            return (int)$available_memory_array[1];
        } else {
            return "Unknown";
        }
    }

    public function total_memory_usage() {
        if ($this->operationSystem == "Linux") {
            $free = shell_exec('free');
            $free = trim($free);
            $free_arr = explode("\n", $free);
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);
            return $mem[1];
        } elseif ($this->operationSystem == "WINNT") {
            $total_memory_command = shell_exec('wmic ComputerSystem get TotalPhysicalMemory');
            $total_memory_array = explode("\n", $total_memory_command);

            return (int)$total_memory_array[1] / 1024;
        } else {
            return "Unknown";
        }
    }
}
