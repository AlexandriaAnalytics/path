<body>
    <div>
        <div class="filtro"></div>
        <div class="container">
            <div class="title-container">
                <h1 class="title">Welcome</h1>
                <h2 class="subtitle">Sinapsis ™</h2>
            </div>

            <main></main>
        </div>
    </div>
</body>

<style>
    @import url("https://use.typekit.net/rjv6btq.css");

    body {
        display: flex;
        align-items: center;
        justify-content: end;
        padding-right: 10%;
        height: 100vh;
        width: 100vw;
        font-family: "skolar-sans-latin", sans-serif;
        font-weight: 400;
        font-style: normal;
        background: url('../images/fondo-login.jpg') center/cover no-repeat;
    }

    .filtro {
        background-color: #22526d;
        width: 100vw;
        height: 100vh;
        position: absolute;
        top: 0;
        left: 0;
        filter: opacity(.3);
        z-index: -1;
    }

    main {
        z-index: 10;
    }

    .container {
        width: 50vw;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        overflow: hidden;
        position: relative;
        z-index: 10;
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
</style>
