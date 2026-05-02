        @page {
            margin: 20mm 15mm;
        }

        body {
            margin: 0;
            color: <?= $theme['text'] ?>;
            font-family: <?= $theme['font'] ?>, Arial, sans-serif;
            font-size: <?= $fontSize ?>px;
            line-height: 1.45;
            width: 100%;
            box-sizing: border-box;
        }

        .sheet {
            width: 100%;
            max-width: 100%;
            border: 0;
            border-radius: 0;
            overflow: visible;
            box-sizing: border-box;
        }

        .header {
            background: <?= $theme['soft'] ?>;
            border-bottom: 2px solid <?= $theme['accent'] ?>;
            padding: 13px 14px 11px;
            box-sizing: border-box;
        }

        .name {
            margin: 0;
            font-size: 22px;
            line-height: 1.1;
            font-weight: 700;
            color: <?= $theme['header_text'] ?>;
        }

        .headline {
            margin: 3px 0 0;
            font-size: <?= max(11, $fontSize) ?>px;
            color: <?= $theme['header_text'] ?>;
        }

        .meta {
            margin: 4px 0 0;
            color: <?= $theme['header_text'] ?>;
            opacity: .82;
            font-size: <?= max(9, $fontSize - 1) ?>px;
        }

        .contact {
            margin: 5px 0 0;
            color: <?= $theme['header_text'] ?>;
            opacity: .82;
            font-size: <?= max(9, $fontSize - 1) ?>px;
            overflow-wrap: anywhere;
        }

        .body {
            padding: 12px 14px 13px;
            box-sizing: border-box;
        }

        .columns::after {
            content: "";
            display: block;
            clear: both;
        }

        .column {
            box-sizing: border-box;
        }

        .columns.columns-25-75 .column-main {
            float: right;
            width: 75%;
            padding-left: 8px;
        }

        .columns.columns-25-75 .column-side {
            float: left;
            width: 25%;
            padding-right: 8px;
        }

        .columns.columns-75-25 .column-main {
            float: left;
            width: 75%;
            padding-right: 8px;
        }

        .columns.columns-75-25 .column-side {
            float: right;
            width: 25%;
            padding-left: 8px;
        }

        .section-full {
            clear: both;
            width: 100%;
            box-sizing: border-box;
        }

        .platform-list {
            margin: 0;
            padding-left: 14px;
            font-size: <?= max(8, $fontSize - 2) ?>px;
            color: <?= $theme['muted'] ?>;
        }

        .platform-list li {
            margin-bottom: 2px;
        }

        .platform-link {
            color: <?= $theme['accent'] ?>;
            text-decoration: none;
        }

        .section {
            margin-bottom: 8px;
            page-break-inside: auto;
            break-inside: auto;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            margin: 0 0 6px;
            border-bottom: 1px solid <?= $theme['line'] ?>;
            padding-bottom: 3px;
            color: <?= $theme['accent'] ?>;
            font-size: <?= max(9, $fontSize - 1) ?>px;
            text-transform: uppercase;
            letter-spacing: .8px;
            font-weight: 700;
            page-break-after: avoid;
            break-after: avoid-page;
        }

        .item {
            margin-bottom: 6px;
            page-break-inside: auto;
            break-inside: auto;
        }

        .item:last-child {
            margin-bottom: 0;
        }

        .item-title {
            margin: 0;
            font-size: <?= max(10, $fontSize) ?>px;
            font-weight: 700;
            color: <?= $theme['text'] ?>;
        }

        .item-subtitle {
            margin: 1px 0 0;
            color: <?= $theme['muted'] ?>;
            font-size: <?= max(9, $fontSize - 1) ?>px;
        }

        .item-period {
            margin: 1px 0 0;
            color: <?= $theme['muted'] ?>;
            font-size: <?= max(8, $fontSize - 2) ?>px;
        }

        p {
            margin: 0 0 4px;
            overflow-wrap: anywhere;
        }

        ul {
            margin: 2px 0 0;
            padding-left: 16px;
            page-break-inside: auto;
            break-inside: auto;
        }

        li {
            margin-bottom: 2px;
            overflow-wrap: anywhere;
        }

        .muted {
            color: <?= $theme['muted'] ?>;
        }

        @media print {
            html,
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .sheet {
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }
        }
    
