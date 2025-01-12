<?php
class DeviceRecognition {
    private $devicePatterns;

    public function __construct() {
        // รูปแบบการใช้งานพลังงานของอุปกรณ์ที่รู้จัก
        $this->devicePatterns = [
            'laptop' => [50, 100], // พลังงานในช่วง 50-100 วัตต์
            'air_conditioner' => [1000, 1500], // พลังงานในช่วง 1000-1500 วัตต์
            'fridge' => [100, 200], // พลังงานในช่วง 100-200 วัตต์
        ];
    }

    /**
     * ระบุอุปกรณ์จากการใช้พลังงาน
     * @param float $energyUsage พลังงานที่ใช้งาน
     * @return string
     */
    public function recognizeDevice($energyUsage) {
        foreach ($this->devicePatterns as $device => $range) {
            if ($energyUsage >= $range[0] && $energyUsage <= $range[1]) {
                return $device;
            }
        }
        return 'unknown'; // หากไม่พบอุปกรณ์ที่ตรงกัน
    }

    /**
     * แสดงผลการวิเคราะห์
     * @param float $energyUsage พลังงานที่ใช้งาน
     */
    public function analyze($energyUsage) {
        $device = $this->recognizeDevice($energyUsage);
        echo "Energy usage ($energyUsage) is identified as: $device.";
    }
}

// ตัวอย่างการใช้งาน
$deviceRecognizer = new DeviceRecognition();
$deviceRecognizer->analyze(120); // ทดสอบด้วยพลังงาน 120
?>
