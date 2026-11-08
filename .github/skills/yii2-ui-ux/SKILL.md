---
name: yii2-ui-ux
description: ปรับแต่งหน้าตาและประสบการณ์ผู้ใช้งานสำหรับเฟรมเวิร์ก Yii2 Advanced Template
triggers: ["ปรับหน้าตา yii2", "แก้ layout frontend", "update yii2 view", "style activeform"]
---

# Yii2 UI/UX Craftsmanship Guidelines

## 1. View & Layout Architecture
- **Multi-File Context:** เมื่อทำการแก้ไข ให้พิจารณาความเชื่อมโยงระหว่าง View (`views/*`), Controller และ AssetBundle (`assets/*`) เสมอ
- **Advanced Template Isolation:** แยกแยะบริบทระหว่าง `frontend` (เน้นความสวยงาม ลื่นไหล เข้าถึงง่าย) และ `backend` (เน้นความกระชับ จัดระเบียบข้อมูลแดชบอร์ดให้ชัดเจน) อย่างเด็ดขาด

## 2. Widget & Asset Enhancement
- **Semantic ActiveForm:** เมื่อปรับปรุงฟอร์ม ให้ใช้ `yii\widgets\ActiveForm` หรือ `yii\bootstrap5\ActiveForm` โดยจัดโครงสร้าง Grid (`row`, `col-*`) ให้ชัดเจน มี `:focus-visible` และซ่อน/แสดง Error Message อย่างเป็นมิตร
- **Asset Bundle Loading:** ห้ามเขียนสไตล์ CSS หรือ JavaScript ดิบลงในไฟล์ View ตรงๆ (Inline) ให้ใช้วิธีลงทะเบียนผ่าน AssetBundle หรือใช้ `$this->registerJs()` / `$this->registerCss()` ในกรณีที่จำเป็นเท่านั้น
- **Data Presentation:** การปรับ UX ของ `GridView` และ `ListView` ต้องรองรับ Responsive เสมอ (ใช้คลาสเทเบิลแบบสไลด์ หรือแปลงเป็น Card Layout ในหน้าจอขนาดเล็ก)

## 3. Frontend & Modernization Rules
- **Modern Layouts:** เปลี่ยนการจัด Layout แบบดั้งเดิม ให้หันมาใช้ Flexbox และ CSS Grid บนคลาสพื้นฐานเพื่อความยืดหยุ่น
- **Performance UX:** คำนึงถึงความเร็วในการโหลดหน้าจอ (Loading States) สำหรับปุ่มที่กดส่งข้อมูลหรือเรียกใช้ dynamic content
- **Accessibility:** ตรวจสอบให้แน่ใจว่าองค์ประกอบ UI สามารถเข้าถึงได้ด้วยคีย์บอร์ดและรองรับ Screen Reader อย่างเหมาะสม
- **Responsive Design:** ทุกองค์ประกอบ UI ต้องปรับตัวได้ตามขนาดหน้าจอ เพื่อให้ผู้ใช้งานบนอุปกรณ์ต่างๆ มีประสบการณ์ที่ดีเท่าเทียมกัน
- **Consistent Styling:** รักษาความสม่ำเสมอของสไตล์ เช่น สี ฟอนต์ และระยะห่าง เพื่อให้ UI ดูเป็นหนึ่งเดียวและใช้งานง่าย
- **User Feedback:** ให้ความสำคัญกับการแสดง Feedback แก่ผู้ใช้งาน เช่น การแสดงข้อความยืนยัน การแจ้งข้อผิดพลาด หรือการแสดงสถานะการโหลด เพื่อให้ผู้ใช้งานรับรู้ถึงผลลัพธ์ของการกระทำของตนอย่างชัดเจน
- **Interactive Elements:** ตรวจสอบให้แน่ใจว่าองค์ประกอบที่ผู้ใช้งานสามารถโต้ตอบได้ เช่น ปุ่ม ลิงก์ และฟอร์ม มีสถานะโต้ตอบที่ชัดเจน (Hover, Active, Focus) เพื่อให้ผู้ใช้งานรับรู้ถึงความสามารถในการโต้ตอบ
- **Error Handling:** จัดการข้อผิดพลาดอย่างชัดเจนและเป็นมิตรต่อผู้ใช้งาน โดยแสดงข้อความข้อผิดพลาดที่เข้าใจง่ายและแนะนำวิธีแก้ไขปัญหาอย่างเหมาะสม
- **Form Validation:** ตรวจสอบให้แน่ใจว่าฟอร์มมีการตรวจสอบข้อมูลทั้งฝั่ง Client และ Server และแสดงข้อความข้อผิดพลาดอย่างชัดเจนเพื่อให้ผู้ใช้งานสามารถแก้ไขได้ทันที
- **Progressive Enhancement:** ออกแบบ UI ให้สามารถใช้งานได้แม้ในกรณีที่ JavaScript ถูกปิดใช้งาน โดยยังคงความสามารถพื้นฐานของฟอร์มและการนำทางอย่างครบถ้วน
- **Cross-Browser Compatibility:** ตรวจสอบให้แน่ใจว่า UI ทำงานได้อย่างถูกต้องในเบราว์เซอร์หลักทั้งหมด รวมถึงการจัดการกับความแตกต่างของ CSS และ JavaScript อย่างเหมาะสม
- **Security Considerations:** พิจารณาประเด็นด้านความปลอดภัย เช่น การป้องกัน XSS และ CSRF ในการออกแบบ UI/UX เพื่อให้ผู้ใช้งานปลอดภัยในการใช้งานระบบ
