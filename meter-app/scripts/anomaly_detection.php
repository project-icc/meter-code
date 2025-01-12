<?php
class AnomalyDetection {
    private $threshold;

    public function __construct($threshold = 100) {
        $this->threshold = $threshold; // เกณฑ์พลังงานที่ถือว่าผิดปกติ
    }

    /**
     * ตรวจจับการใช้งานผิดปกติ
     * @param float $energyUsage พลังงานที่ใช้งาน
     * @return bool
     */
    public function detectAnomaly($energyUsage) {
        if ($energyUsage > $this->threshold) {
            return true; // ถือว่าผิดปกติ
        }
        return false; // ถือว่าปกติ
    }

    /**
     * แสดงผลการวิเคราะห์
     * @param float $energyUsage พลังงานที่ใช้งาน
     */
    public function analyze($energyUsage) {
        if ($this->detectAnomaly($energyUsage)) {
            echo "Anomaly Detected! Energy usage ($energyUsage) exceeds the threshold ({$this->threshold}).";
        } else {
            echo "Energy usage ($energyUsage) is within normal limits.";
        }
    }
}

// ตัวอย่างการใช้งาน
$anomalyDetector = new AnomalyDetection(120); // ตั้งค่าเกณฑ์เป็น 120
$anomalyDetector->analyze(130); // ทดสอบด้วยพลังงาน 130
?>
