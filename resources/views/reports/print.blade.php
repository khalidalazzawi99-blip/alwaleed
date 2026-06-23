<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">

<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

<style>
*{box-sizing:border-box}

body{
    margin:0;
    font-family:'Tajawal',sans-serif;
    background:#F6F4F1;
    color:#1F1F1F;
}

.page{
    width:210mm;
    min-height:297mm;
    margin:20px auto;
    background:#fff;
    padding:24mm 18mm;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:2px solid #CDBA9E;
    padding-bottom:18px;
    margin-bottom:24px;
}

.logo img{
    width:150px;
}

.report-title{
    text-align:left;
}

.report-title h1{
    margin:0;
    font-size:30px;
    color:#1F1F1F;
}

.report-title p{
    margin:6px 0 0;
    color:#8A8178;
}

.summary{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-bottom:26px;
}

.summary-card{
    border:1px solid #ECE7E1;
    border-radius:16px;
    padding:16px;
    background:#FAFAFA;
}

.summary-card span{
    color:#8A8178;
    font-size:13px;
}

.summary-card h2{
    margin:8px 0 0;
    font-size:20px;
}

.section{
    margin-top:26px;
}

.section h2{
    font-size:20px;
    margin-bottom:12px;
    color:#1F1F1F;
}

table{
    width:100%;
    border-collapse:collapse;
    font-size:14px;
}

th{
    background:#F5F1EB;
    color:#6B5D4B;
    padding:10px;
    border:1px solid #E6DED4;
}

td{
    padding:10px;
    border:1px solid #EDE7DF;
}

tr:nth-child(even) td{
    background:#FAFAFA;
}

.green{color:#15803D;font-weight:800}
.red{color:#B91C1C;font-weight:800}
.gold{color:#A68A64;font-weight:800}

.footer{
    margin-top:35px;
    padding-top:14px;
    border-top:1px solid #ECE7E1;
    display:flex;
    justify-content:space-between;
    color:#8A8178;
    font-size:13px;
}

.print-btn{
    text-align:center;
    margin:20px;
}

button{
    background:#CDBA9E;
    color:white;
    border:0;
    padding:14px 34px;
    border-radius:12px;
    font-family:'Tajawal',sans-serif;
    font-weight:800;
    cursor:pointer;
}

@media print{
    body{
        background:white;
    }

    .page{
        width:100%;
        min-height:auto;
        margin:0;
        padding:14mm;
    }

    .print-btn{
        display:none;
    }

    table{
        page-break-inside:auto;
    }

    tr{
        page-break-inside:avoid;
        page-break-after:auto;
    }

    .section{
        page-break-inside:avoid;
    }
}
</style>
</head>

<body>

<div class="page">

    <div class="header">
        <div class="logo">
            <img src="/logo.png">
        </div>

        <div class="report-title">
            <h1>تقرير النظام</h1>
            <p>Al Waleed </p>
            <p>تاريخ الطباعة: {{ date('Y-m-d') }}</p>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <span>الرصيد الحالي</span>
            <h2 class="gold">{{ number_format($balance,2) }}</h2>
        </div>

        <div class="summary-card">
            <span>إجمالي القبض</span>
            <h2 class="green">{{ number_format($totalReceipts,2) }}</h2>
        </div>

        <div class="summary-card">
            <span>إجمالي الصرف</span>
            <h2 class="red">{{ number_format($totalPayments,2) }}</h2>
        </div>

        <div class="summary-card">
            <span>صافي الحركة</span>
            <h2>{{ number_format($totalReceipts - $totalPayments,2) }}</h2>
        </div>
    </div>

    <div class="section">
        <h2>آخر سندات القبض</h2>

        <table>
            <thead>
                <tr>
                    <th>رقم الوصل</th>
                    <th>الزبون</th>
                    <th>المبلغ</th>
                </tr>
            </thead>

            <tbody>
            @foreach($receipts->take(12) as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_no }}</td>
                    <td>{{ $receipt->customer->name ?? '-' }}</td>
                    <td class="green">{{ number_format($receipt->amount,2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>آخر سندات الصرف</h2>

        <table>
            <thead>
                <tr>
                    <th>رقم الوصل</th>
                    <th>المورد</th>
                    <th>المبلغ</th>
                </tr>
            </thead>

            <tbody>
            @foreach($payments->take(12) as $payment)
                <tr>
                    <td>{{ $payment->payment_no }}</td>
                    <td>{{ $payment->supplier->name ?? '-' }}</td>
                    <td class="red">{{ number_format($payment->amount,2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
<div class="section">

    <h2>حركة الصندوق اليومية</h2>

    <table>

        <thead>

            <tr>
                <th>التاريخ</th>
                <th>الداخل</th>
                <th>الخارج</th>
                <th>الصافي</th>
            </tr>

        </thead>

        <tbody>

        @foreach($cashMovement as $row)

            <tr>

                <td>{{ $row['date'] }}</td>

                <td class="green">
                    {{ number_format($row['in'],2) }}
                </td>

                <td class="red">
                    {{ number_format($row['out'],2) }}
                </td>

                <td>
                    {{ number_format($row['net'],2) }}
                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

</div>
    <div class="footer">
        <span>Al Waleed </span>
        <span>Accounts & Business Management System</span>
    </div>

</div>

<div class="print-btn">
    <button onclick="window.print()">طباعة / حفظ PDF</button>
</div>

</body>
</html>