

# Payment Diagram

```bash
Request → Validasi → Upload File (opsional)
          |
          v
      Buat Transaction
          |
          v
Jika patient_id ada? → Ya → Cari AmountService → Buat Payment
          |                       |
          |                       v
          |                   Gagal? → rollback Transaction
          |
          v
Update Screening.payment_status
Update Patients.payment_status (opsional)
          |
          v
      Return JSON Response
```