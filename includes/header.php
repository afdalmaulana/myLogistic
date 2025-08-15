<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            background-image: linear-gradient(to bottom left, rgba(14, 36, 112, 1), rgba(28, 29, 35, 0.95));
            /* jika kamu ingin mencegah scroll sama sekali */
        }

        .nav {
            background: none;
            color: wheat;
            width: 100%;
            padding: 20px 50px;
            transition: margin-left 0.3s ease;
        }

        .nav-left {
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            font-weight: 800;
            color: white;
        }

        .nav-right {
            display: flex;
            flex-direction: row;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
        }


        /* Card */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .dashboard-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease;
            box-shadow: ;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .dashboard-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .dashboard-count {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .dashboard-title {
            font-size: 18px;
            color: #555;
            margin-bottom: 15px;
        }

        .dashboard-link {
            margin-top: auto;
            text-align: right;
        }

        .dashboard-link a {
            background-color: #007bff;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }

        .dashboard-link a:hover {
            background-color: #0056b3;
        }

        .orange {
            color: orange;
        }

        .red {
            color: red;
        }

        /* BATAS CARD */
        .logoutBtn {
            display: block;
            /* biar a bisa lebar penuh */
            padding: 8px 8px;
            color: white;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s ease;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            background-color: red;
        }

        .logoutBtn:hover {
            background-color: #ac1106ff;
            color: white;
            transition: 0.2s ease-in-out;
        }

        .menu-label {
            padding: 10px 16px;
            font-weight: bold;
            color: #ccc;
            /* atau warna lain agar terlihat bukan tombol */
            cursor: default;
        }

        #menu-surat>div:not(:first-child) {
            padding-left: 16px;
        }

        #menu-logistik>div:not(:first-child) {
            padding-left: 16px;
        }

        .isinavbar {
            width: 100%;
            display: flex;
            justify-content: space-between;
            /* align-items: center; */
        }


        .dashboard-menu {
            /* background-image: linear-gradient(to bottom left, rgba(14, 36, 112, 1), rgba(28, 29, 35, 0.95)); */
            width: 100%;
            height: 100vh;
        }

        #dashmenu-item {
            display: flex;
            flex-direction: row;
            /* padding: 20px 30px; */
            flex-wrap: wrap;
            /* agar baris baru otomatis jika sempit */
            gap: 20px;
        }

        .dash-list {
            background: #00529c;
            color: white;
            border-radius: 12px;
            padding: 10px 10px 12px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 20px;
            flex: 1;
            /* biar semua elemen berbagi ruang sama rata */
            min-width: 220px;
            /* agar tidak terlalu kecil saat layar sempit */
            height: 180px;
            /* atur sesuai seberapa "memanjang" yang kamu mau */

        }

        .dash-list-inmail {
            background: rgba(255, 255, 255, 0.8);
            /* Warna putih transparan */
            backdrop-filter: blur(2000px);
            /* Efek blur di belakang */
            -webkit-backdrop-filter: blur(1000px);
            /* Untuk Safari */
            background-image: linear-gradient(to bottom right, rgba(255, 255, 255, 0.8), rgba(163, 157, 157, 0.74));
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.98);
            /* Opsional, biar lebih pop */
            color: black;
            border-radius: 12px;
            padding: 10px 10px 12px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 8px;
            flex: 1;
            /* biar semua elemen berbagi ruang sama rata */
            min-width: 220px;
            /* agar tidak terlalu kecil saat layar sempit */
            height: 180px;
            /* atur sesuai seberapa "memanjang" yang kamu mau */

        }

        .dash-list-outmail {
            background: rgba(255, 255, 255, 0.8);
            /* Warna putih transparan */
            backdrop-filter: blur(2000px);
            /* Efek blur di belakang */
            -webkit-backdrop-filter: blur(1000px);
            /* Untuk Safari */
            background-image: linear-gradient(to bottom right, rgba(255, 255, 255, 0.8), rgba(163, 157, 157, 0.74));
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.98);
            /* Opsional, biar lebih pop */
            color: black;
            border-radius: 12px;
            padding: 10px 10px 12px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 8px;
            flex: 1;
            /* biar semua elemen berbagi ruang sama rata */
            min-width: 220px;
            /* agar tidak terlalu kecil saat layar sempit */
            height: 180px;
            /* atur sesuai seberapa "memanjang" yang kamu mau */

        }

        .dash-list-inlogistic {
            background: rgba(255, 255, 255, 0.8);
            /* Warna putih transparan */
            backdrop-filter: blur(2000px);
            /* Efek blur di belakang */
            -webkit-backdrop-filter: blur(1000px);
            /* Untuk Safari */
            background-image: linear-gradient(to bottom right, rgba(255, 255, 255, 0.8), rgba(163, 157, 157, 0.74));
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.98);
            /* Opsional, biar lebih pop */
            color: black;
            border-radius: 12px;
            padding: 10px 10px 12px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 8px;
            flex: 1;
            /* biar semua elemen berbagi ruang sama rata */
            min-width: 220px;
            /* agar tidak terlalu kecil saat layar sempit */
            height: 180px;
        }


        .dash-list-outlogistic {
            background: rgba(255, 255, 255, 0.8);
            /* Warna putih transparan */
            backdrop-filter: blur(2000px);
            /* Efek blur di belakang */
            -webkit-backdrop-filter: blur(1000px);
            /* Untuk Safari */
            background-image: linear-gradient(to bottom right, rgba(255, 255, 255, 0.8), rgba(163, 157, 157, 0.74));
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.98);
            /* Opsional, biar lebih pop */
            color: black;
            border-radius: 12px;
            padding: 10px 10px 12px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 8px;
            flex: 1;
            /* biar semua elemen berbagi ruang sama rata */
            min-width: 220px;
            /* agar tidak terlalu kecil saat layar sempit */
            height: 180px;
            /* atur sesuai seberapa "memanjang" yang kamu mau */
        }


        /* Dropdown  */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s ease-in-out, visibility 0.2s;
            position: absolute;
            top: 100%;
            left: 0;
            background: #31373e;
            min-width: 120px;
            font-size: 16px;
            z-index: 999;
            border-radius: 12px;
            overflow: hidden;
            /* margin-top: 4px; */
            pointer-events: none;
            /* agar tidak bisa di-hover saat tidak terlihat */
        }


        .dropdown-content a {
            color: #e5e6e8ff;
            padding: 10px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
            font-size: 12px;
            font-weight: 300;
        }

        .dropdown-content a:hover {
            /* background: #31373e; */
            color: #00529c;
            border-radius: 10px;
            transition: 0.2s ease-in-out;
        }

        #dropdownMenuButton {
            /* background: orange; */
            color: black;
        }

        .button-dropdown {
            padding: 8px 22px;
            /* border: 5px solid #31373e; */
            background: none;
            color: #f1f1f1;
            border: none;
            font-weight: 200;
        }

        .button-dropdown.dropdown-toggle:hover {
            color: white !important;
        }

        /* INI BAGIAN UTAMA UNTUK HOVER */
        .dropdown:hover .dropdown-content {
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
        }

        /* Batas Dropdown */


        /*CSS BUTTON */
        .button-list {
            border: none;
            border: #31373e;
            background-color: #31373e;
            color: #ffffffd2;
            padding: 6px 10px 6px 60px;
            border-radius: 10px;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            text-decoration: none;
        }

        .button-lihat {
            padding: 6px;
            border-radius: 6px;
            background: #00529c;
            color: white;
            text-decoration: none;
        }

        .button-lihat:hover {
            background: #1ac447;
            color: white;
            transition: 0.2s ease-in-out;
        }

        .button-list:hover {
            color: white;
            background: #153E76;
            transition: 0.3s ease-in-out;
        }

        .button-trash {
            background: none;
            border: none;
            font-size: 16px;
            color: red;
            padding: 6px 10px 6px 10px;
            border-radius: 10px;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            text-decoration: none;
        }

        .button-approve {
            background: none;
            border: none;
            font-size: 16px;
            color: black;
            padding: 6px 10px 6px 10px;
            border-radius: 10px;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            text-decoration: none;
        }

        .button-reject {
            background: none;
            border: none;
            font-size: 16px;
            color: black;
            padding: 6px 10px 6px 10px;
            border-radius: 10px;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            text-decoration: none;
        }

        .button-save {
            background: none;
            border: none;
            font-size: 16px;
            color: black;
            padding: 6px 10px 6px 10px;
            border-radius: 10px;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            text-decoration: none;
        }

        .button-save:hover {
            background: green;
            color: white;
            transition: 0.2s ease-in-out;
        }

        .button-trash:hover {
            background: red;
            color: white;
            transition: 0.2s ease-in-out;
        }

        .button-approve:hover {
            background: #1ac447;
            color: white;
            transition: 0.2s ease-in-out;
        }

        .button-reject:hover {
            background: #c4251aff;
            color: white;
            transition: 0.2s ease-in-out;
        }

        .button-delete {
            background-color: #ebecec;
            font-size: 28px;
            color: red;
            padding: 6px 10px 6px 60px;
            border-radius: 10px;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            text-decoration: none;
        }

        .dashboard-button {
            border-bottom: 2px solid black;
            padding: 12px 12px 2px 12px;
            display: flex;
            flex-direction: row;
            gap: 16px;
        }

        .dashboard-button button {
            background: none;
            border: none;
            padding: 0;
            margin: 0;
            cursor: pointer;
        }

        .dashboard-button #button-list {
            color: black;
            font-size: 28px;
        }


        .button-send {
            width: 100%;
            border-radius: 10px;
            background: #1ac447;
            color: white;
        }

        .button-send:hover {
            color: white;
            background: green;
            transition: 0.2s ease-in-out;
        }

        .button-signin {
            width: 100%;
            border-radius: 10px;
            background: #00529c;
            color: white;
            border: none;
            padding: 6px;
        }

        .button-signin:hover {
            color: white;
            background: #1ac447;
            transition: 0.2s ease-in-out;
        }

        .button-log {
            background: #00529c;
            text-decoration: none;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            height: 100%;
            /* width: 100%; */
            font-size: 12px;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }

        .button-log:hover {
            transition: 0.2s ease-in-out;
            color: white;
            background: green;
        }

        .button-ryc:hover {
            color: white;
            background: red;
            transition: 0.3s ease-in-out;
        }

        #signinbutton {
            font-size: 16px;
            border: none;
            padding: 4px 16px 4px 16px;
            border: #31373e;
            background-color: #00529c;
            color: white;
            border-radius: 10px;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
        }

        #signinbutton:hover {
            background-color: #00529c;
            border: #31373e;
            border: 2px;
            color: black;
            transition: 0.2s ease-in-out;
        }

        /*-----BATAS CSS BUTTON ----- */

        .dashboard-mailin {
            padding: 10px;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 0;
            /* gap: 5px; */
            /* max-width: 500px; */
        }



        .sub-menu {
            padding-bottom: 10px;
        }

        .table-container {
            border: 3px solid #00529c;
            width: 100%;
            /* border-collapse: collapse; */
            table-layout: fixed;
            max-height: 350px;
            overflow-x: auto;
            overflow-y: auto;
            border-radius: 8px;
            z-index: 0;
        }

        /* Sticky header agar tetap terlihat saat scroll */
        .table-container thead th {
            position: sticky;
            top: 0;
            background-color: #00529c;
            color: white;
            z-index: 2;
            padding: 12px;
            box-sizing: border-box;

        }

        .table-container table th,
        .table-container table td {
            padding: 12px;
            /* border: 1px solid #ddd; */
            overflow: auto;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }


        .inmail {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .outmail {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .stocks {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .list-mailinvent {
            margin-top: 14px;
        }

        .mail-count {
            border: 10px solid #00529c;
            padding: 12px;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .form-input {
            border: 6px solid #00529c;
            padding: 12px;
            /* max-height: 550px; */
            overflow-y: auto;
            border-radius: 8px;
            text-align: center;
            justify-content: center;
        }


        /* FORM LOGIN */
        .form-input-login {
            /* border: 6px solid #dbe5edff; */
            padding: 60px 40px 10px 40px;
            min-height: 400px;
            min-width: 400px;
            color: white;
            overflow-y: auto;
            border-radius: 20px;
            text-align: center;
            justify-content: center;
            justify-items: center;
            align-items: center;
            /* background-image: linear-gradient(#dbe5edff, #dfe7edff, #dbe5edff); */
            background: rgba(25, 25, 35, 0.45);
            /* Dark navy-like with transparency */
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);

            /* Soft shadow */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
        }

        .dashboard_login {
            padding: 30px;
            font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            /* gap: 5px; */
            max-width: 600px;
        }

        .login-wrapper {
            background-image: linear-gradient(#00529c, #1d2b38ff, #00529c);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signup-wrapper {
            /* background-image: linear-gradient(#00529c, #1d2b38ff, #00529c); */
            background: #02080ece;
            top: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            /* align-items: center; */
        }

        /* BATAS FORM LOGIN */


        .status-pending {
            /* merah muda */
            color: #eb7e02ff;
            /* teks merah gelap */
            font-weight: bold;
        }

        .status-approved {
            /* hijau muda */
            color: #155724;
            /* teks hijau gelap */
            font-weight: bold;
        }

        .status-rejected {
            /* abu-abu */
            color: #ff0000ff;
            /* teks abu gelap */
            font-weight: bold;
        }

        .status-forward {
            color: #ffee00ff;
            /* teks abu gelap */
            font-weight: bold;
        }

        .input-mail {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .list-input {
            padding: 8px 12px;
            border-radius: 10px;
            /* background-color: #00529c; */
            color: black;
            border: 2px solid black;
            cursor: pointer;
        }

        .list-input:focus {
            outline: none;
            border: none;
            margin-top: 1px;
            box-shadow: 0 0 0 4px #00529c;
            transition: 0.2s ease-in-out;
        }


        @media (max-width: 992px) {
            .dash-list {
                flex: 1 1 45%;
                /* 2 per baris */
            }
        }

        @media (max-width: 600px) {
            .dash-list {
                flex: 1 1 100%;
                /* 1 per baris */
            }
        }
    </style>
</head>

<body>