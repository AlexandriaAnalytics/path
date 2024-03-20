<body>
    <div class="title-container">
        <h1 class="title">Welcome</h1>
        <h2 class="subtitle">Sinapsis ™</h2>
    </div>
</body>

<style>
    @import url("https://use.typekit.net/rjv6btq.css");

    body {
        display: flex;
        align-items: center;
        height: 100vh;
        width: 100vw;
        font-family: "skolar-sans-latin", sans-serif;
        font-weight: 400;
        font-style: normal;
        background: url('../images/fondo-login-filtro.jpg') center/cover no-repeat;
        overflow: hidden;
    }

    /* .container {
        width: 50vw;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        overflow: hidden;
        position: relative;
        z-index: 10;
    } */

    main {
        position: absolute;
        right: 5%;
    }

    .title-container {
        display: grid;
        place-content: center;
        position: fixed;
        right: 40%;
        top: 40%;
    }

    .title {
        color: #fff;
        font-size: 4.5rem;
        border-bottom: 1px solid #fff;
        margin-bottom: 6%;
        animation-duration: 2s;
        animation-name: slidein;
    }

    @keyframes slidein {
        0% {
            margin-left: 100%;
            width: 120%;
        }

        50% {
            width: 170%;
        }

        100% {
            margin-left: 0%;
            width: 100%;
        }
    }

    .subtitle {
        color: #fff;
        background-color: #22526d;
        padding: 2%;
        font-size: 1.1rem;
        animation-duration: 2s;
        animation-name: slidein;
    }

    @media only screen and (max-width: 1400px) {
        .title-container {
            position: fixed;
            left: 15%;
        }
    }

    @media only screen and (max-width: 1100px) {
        body {
            justify-content: center;
            padding-right: 0%;
            background: url('../images/fondo-login.jpg') 15% no-repeat;
            background-size: cover;
        }

        main {
            position: fixed;
            top: 20%;
        }

        .title-container {
            position: fixed;
            top: 1%;
            left: 40%;
        }
    }
</style>
