<?php
class MLModel {
    private $weights;
    private $bias;

    public function __construct() {
        // กำหนดค่าของโมเดล (ตัวอย่างง่าย)
        $this->weights = [0.5, 1.2, -0.3]; // ค่าน้ำหนักสำหรับฟีเจอร์
        $this->bias = 2.5; // ค่าคงที่ (bias)
    }

    /**
     * พยากรณ์การใช้พลังงาน
     * @param array $features ฟีเจอร์สำหรับโมเดล (เช่น [เวลา, วันที่, ความถี่การใช้งาน])
     * @return float ผลการพยากรณ์
     */
    public function predict($features) {
        $prediction = $this->bias;
        foreach ($features as $i => $value) {
            $prediction += $this->weights[$i] * $value;
        }
        return $prediction;
    }

    /**
     * แสดงผลการพยากรณ์
     * @param array $features ฟีเจอร์สำหรับโมเดล
     */
    public function analyze($features) {
        $prediction = $this->predict($features);
        echo "Predicted energy usage based on features " . implode(', ', $features) . ": $prediction";
    }
}

// ตัวอย่างการใช้งาน
$mlModel = new MLModel();
$mlModel->analyze([10, 5, 2]); // ฟีเจอร์: [เวลา, วันที่, ความถี่การใช้งาน]
?>
