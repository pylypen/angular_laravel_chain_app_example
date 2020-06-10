<!DOCTYPE HTML>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ServeHub | Certificate of Completion</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
            width: 1056px;
            height: 816px;
        }

        body {
            font-family: 'roboto', sans-serif;
            font-size: 16px;
            color: #353c3f;
            position:fixed;
            left: 0;
            top: 0;
            padding: 0;
            margin: 0;
            text-align: center;
            z-index:0;
            width: 1056px;
            height: 816px;
        }

        .cert {
            background-image: url('/images/certificates/background.jpg');
            background-repeat: no-repeat;
            background-position: center;
            background-image-resize: 6;
            width: 100%;
            height: 100%;
            margin: 0;
            padding-top: 50px;
            box-sizing: border-box;
            z-index:5;
        }
        .cert-content {
            box-sizing: border-box;
            padding: 25px 15px;
            text-align: center;
            margin: 8% auto;
            width: 100%;
            height: 500px;

        }

        .cert-content h2 {
            font-size: 3.25rem;
            letter-spacing: 0.25rem;
        }

        .cert-content h3 {
            font-size: 1.85rem;
        }

        .cert-content h4 {
            font-size: 1.15rem;
        }

        .cert-content p {
            font-size: 1.05rem;
        }

        .cert-content .cert-issuer {
        }

        .cert-content .cert-recipient {
            margin: 20px 0;
        }

        .cert-content .course-info {
        }

        .cert-identifier {
            margin: 20px 0 0 0;
            font-size: 0.7rem;
        }

    </style>
</head>

<body>
<!-- fixed elements - should remain at top-level for mPDF -->


<div class="cert">
    <div class="cert-content">
        <div class="cert-issuer">
            <p><strong>{{ $certificate->issued_org_name }}</strong><br/>
                is honored to present this</p>
        </div>
        <div class="cert_recipient">
            <h2>CERTIFICATE OF COMPLETION</h2>
            <p>
                to<br/>
            <h3>{{ $certificate->issued_user_name }}</h3>
            </p>
        </div>

        <div class="course-info">
            <p>for successful completion of<br/>
                <strong>{{ $certificate->issued_course_name }}</strong><br/>
                on <strong>{{ date('F j, Y', strtotime($certificate->updated_at)) }}</strong></p>

                <div class="cert-identifier">CERTIFICATE IDENTIFIER: {{ $certificate->cert_name }}</div>
        </div> <!-- end course-info -->

    </div> <!-- end cert-content -->
</div> <!-- end cert -->


</body>

</html>