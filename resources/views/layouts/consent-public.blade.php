{{--
  Layout delle pagine pubbliche del flusso consensi (form, conferma, link scaduto).
  Autonomo rispetto a layouts.guest: design approvato dal cliente, nessuna
  dipendenza da Bootstrap o dagli asset compilati dell'area autenticata.
--}}
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="robots" content="noindex, nofollow">
<title>@yield('title') – Welfare Nest</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
@verbatim
<style>
  :root{
    --navy:#10354E; --navy-dark:#0B2738; --gold:#DBBF24; --brown:#96423E;
    --eggshell:#F7F3E9; --line:#E4DCCB; --text:#3F5A6B; --muted:#6B7F8B; --white:#FFFFFF;
    --serif:'Playfair Display',Georgia,'Times New Roman',serif;
    --sans:'Poppins','Helvetica Neue',Arial,sans-serif;
  }
  *,*::before,*::after{box-sizing:border-box;}
  html,body{margin:0;}
  body{background:var(--eggshell);color:var(--text);font-family:var(--sans);font-size:15px;line-height:1.65;-webkit-font-smoothing:antialiased;}
  .page{max-width:584px;margin:0 auto;padding:48px 20px 40px;}
  .brand{display:block;width:184px;margin:0 0 32px 48px;}
  .brand svg{display:block;width:100%;height:auto;}

  .card{background:var(--white);border-top:4px solid var(--gold);padding:44px 48px 40px;}
  .card.is-centered{text-align:center;}
  h1{margin:0 0 10px;font-family:var(--serif);font-weight:600;font-size:32px;line-height:1.2;color:var(--navy);}
  .recipient{margin:0 0 34px;color:var(--muted);font-size:14px;}
  .recipient strong{color:var(--navy);font-weight:500;word-break:break-all;}
  .lead{margin:14px 0 0;color:var(--text);}
  .lead:last-child{margin-bottom:0;}

  .alert{margin:0 0 24px;padding:14px 18px;border-left:3px solid var(--brown);background:var(--eggshell);color:var(--brown);font-size:14px;}

  fieldset{border:0;margin:0 0 26px;padding:0;min-width:0;}
  legend{display:flex;align-items:baseline;gap:10px;width:100%;padding:0;margin:0 0 10px;font-size:14px;font-weight:600;color:var(--navy);}
  legend .req{font-weight:400;font-size:12px;color:var(--muted);}

  .option{border:1px solid var(--line);padding:18px 20px;transition:border-color .15s,background-color .15s;}
  .option + .option{margin-top:12px;}
  .option:has(input:checked){border-color:var(--navy);background:var(--eggshell);}
  .option.is-invalid{border-color:var(--brown);}
  .check{display:grid;grid-template-columns:22px 1fr;gap:14px;align-items:start;cursor:pointer;color:var(--navy);font-size:15px;line-height:1.55;}
  .check input{appearance:none;-webkit-appearance:none;margin:1px 0 0;width:22px;height:22px;border:1.5px solid var(--navy);border-radius:3px;background:var(--white) center/14px no-repeat;cursor:pointer;}
  .check input:checked{background-color:var(--navy);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 14 14'%3E%3Cpath d='M2.5 7.4l2.9 2.9 6.1-6.3' fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");}
  .check input:focus-visible{outline:3px solid var(--gold);outline-offset:2px;}
  .doc{display:inline-block;margin:8px 0 0 36px;font-size:13px;color:var(--brown);text-decoration:underline;text-underline-offset:3px;}
  .doc:hover{color:var(--navy);}
  .doc:focus-visible{outline:3px solid var(--gold);outline-offset:2px;}
  .error{margin:8px 0 0;font-size:13px;color:var(--brown);}
  .error:empty{display:none;}

  .submit{display:block;width:100%;margin:34px 0 0;padding:16px 24px;border:0;border-radius:4px;background:var(--navy);color:var(--white);font-family:var(--sans);font-size:16px;font-weight:600;cursor:pointer;transition:background-color .15s;}
  .submit:hover{background:var(--navy-dark);}
  .submit:focus-visible{outline:3px solid var(--gold);outline-offset:3px;}
  .submit[aria-busy="true"]{opacity:.75;cursor:progress;}
  .note{margin:16px 0 0;font-size:13px;line-height:1.6;color:var(--muted);}

  .footer{background:var(--navy);padding:26px 48px 28px;color:#B9C6CE;font-size:12px;line-height:1.7;}
  .footer p{margin:0;}
  .footer .name{margin:0 0 6px;font-family:var(--serif);font-size:16px;font-weight:600;color:var(--eggshell);}

  @media (max-width:560px){
    .page{padding:32px 14px 28px;}
    .brand{width:160px;margin-left:24px;}
    .card{padding:34px 24px 30px;}
    .footer{padding:24px 24px 26px;}
    h1{font-size:27px;}
    .option{padding:16px;}
  }
  @media (prefers-reduced-motion:reduce){*{transition:none !important;}}
</style>
@endverbatim
</head>
<body>
<main class="page">
  <a class="brand" href="https://www.welfarenest.it">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="2.6 10.4 23.5 7.55" role="img" aria-label="Welfare Nest"> <path fill="#DBBF24" d="M 13.128 16.203 L 12.785 16.203 L 11.757 14.640 L 11.757 16.203 L 11.414 16.203 L 11.414 14.113 L 11.757 14.113 L 12.785 15.667 L 12.785 14.113 L 13.128 14.113 Z M 13.128 16.203"/> <path fill="#DBBF24" d="M 15.335 15.226 C 15.335 15.097 15.292 15 15.203 14.921 C 15.113 14.847 15.003 14.808 14.875 14.808 C 14.757 14.808 14.656 14.843 14.574 14.921 C 14.488 14.996 14.437 15.097 14.421 15.226 Z M 15.683 15.503 L 14.421 15.503 C 14.429 15.632 14.480 15.742 14.566 15.820 C 14.656 15.902 14.761 15.941 14.890 15.941 C 15.074 15.941 15.207 15.863 15.281 15.710 L 15.652 15.710 C 15.601 15.863 15.511 15.984 15.378 16.082 C 15.246 16.179 15.085 16.230 14.890 16.230 C 14.734 16.230 14.589 16.191 14.464 16.121 C 14.339 16.050 14.242 15.953 14.171 15.820 C 14.101 15.691 14.066 15.542 14.066 15.371 C 14.066 15.203 14.097 15.050 14.167 14.921 C 14.238 14.792 14.335 14.695 14.460 14.625 C 14.585 14.554 14.730 14.519 14.890 14.519 C 15.046 14.519 15.187 14.554 15.308 14.621 C 15.429 14.691 15.523 14.785 15.593 14.910 C 15.664 15.031 15.695 15.171 15.695 15.335 C 15.695 15.394 15.691 15.453 15.683 15.503"/> <path fill="#DBBF24" d="M 16.882 16.160 C 16.781 16.109 16.699 16.046 16.640 15.964 C 16.578 15.886 16.546 15.792 16.542 15.695 L 16.898 15.695 C 16.902 15.765 16.937 15.824 16.996 15.871 C 17.058 15.917 17.136 15.941 17.226 15.941 C 17.324 15.941 17.398 15.921 17.449 15.886 C 17.503 15.847 17.531 15.800 17.531 15.742 C 17.531 15.679 17.5 15.632 17.441 15.605 C 17.382 15.574 17.289 15.539 17.160 15.503 C 17.039 15.472 16.937 15.437 16.859 15.406 C 16.781 15.375 16.710 15.324 16.656 15.257 C 16.597 15.191 16.570 15.105 16.570 14.996 C 16.570 14.910 16.597 14.828 16.648 14.753 C 16.699 14.683 16.773 14.625 16.871 14.582 C 16.968 14.539 17.082 14.519 17.207 14.519 C 17.394 14.519 17.546 14.566 17.660 14.664 C 17.777 14.757 17.839 14.886 17.847 15.050 L 17.503 15.050 C 17.496 14.976 17.468 14.917 17.414 14.875 C 17.359 14.832 17.285 14.808 17.195 14.808 C 17.105 14.808 17.035 14.824 16.988 14.859 C 16.941 14.894 16.914 14.937 16.914 14.996 C 16.914 15.039 16.929 15.074 16.964 15.105 C 16.996 15.136 17.035 15.160 17.082 15.175 C 17.125 15.191 17.195 15.214 17.285 15.242 C 17.402 15.273 17.503 15.304 17.582 15.339 C 17.656 15.371 17.722 15.417 17.781 15.484 C 17.835 15.546 17.867 15.632 17.867 15.738 C 17.867 15.832 17.839 15.917 17.789 15.992 C 17.738 16.066 17.664 16.125 17.570 16.164 C 17.472 16.207 17.359 16.230 17.234 16.230 C 17.105 16.230 16.988 16.207 16.882 16.160"/> <path fill="#DBBF24" d="M 19.234 14.824 L 19.234 15.742 C 19.234 15.804 19.25 15.847 19.281 15.875 C 19.308 15.902 19.359 15.914 19.429 15.914 L 19.640 15.914 L 19.640 16.203 L 19.371 16.203 C 19.214 16.203 19.097 16.164 19.015 16.093 C 18.933 16.023 18.890 15.906 18.890 15.742 L 18.890 14.824 L 18.695 14.824 L 18.695 14.546 L 18.890 14.546 L 18.890 14.136 L 19.234 14.136 L 19.234 14.546 L 19.640 14.546 L 19.640 14.824 Z M 19.234 14.824"/> <path fill="#0F354E" d="M 13.667 11.355 L 13.093 13.382 L 12.867 13.382 L 12.359 11.652 L 11.835 13.382 L 11.613 13.382 L 11.050 11.355 L 11.269 11.355 L 11.734 13.132 L 12.253 11.355 L 12.480 11.355 L 12.984 13.132 L 13.449 11.355 Z M 13.667 11.355"/> <path fill="#0F354E" d="M 15.714 12.199 C 15.664 12.117 15.597 12.054 15.511 12.011 C 15.429 11.968 15.335 11.949 15.234 11.949 C 15.082 11.949 14.957 11.996 14.847 12.093 C 14.742 12.191 14.683 12.328 14.671 12.503 L 15.785 12.503 C 15.789 12.386 15.765 12.285 15.714 12.199 M 15.984 12.667 L 14.671 12.667 C 14.675 12.789 14.707 12.890 14.757 12.976 C 14.812 13.058 14.878 13.125 14.964 13.167 C 15.050 13.210 15.144 13.234 15.246 13.234 C 15.375 13.234 15.484 13.203 15.574 13.136 C 15.664 13.074 15.726 12.988 15.753 12.878 L 15.968 12.878 C 15.929 13.035 15.847 13.160 15.718 13.257 C 15.593 13.355 15.433 13.406 15.246 13.406 C 15.093 13.406 14.960 13.371 14.843 13.304 C 14.726 13.238 14.632 13.144 14.566 13.019 C 14.5 12.898 14.464 12.753 14.464 12.589 C 14.464 12.425 14.5 12.281 14.562 12.156 C 14.628 12.035 14.722 11.937 14.839 11.875 C 14.957 11.808 15.093 11.773 15.246 11.773 C 15.394 11.773 15.527 11.808 15.640 11.875 C 15.753 11.937 15.839 12.027 15.902 12.140 C 15.960 12.25 15.992 12.375 15.992 12.507 C 15.992 12.578 15.988 12.632 15.984 12.667"/> <path fill="#0F354E" d="M 17.148 13.382 L 16.945 13.382 L 16.945 11.234 L 17.148 11.234 Z M 17.148 13.382"/> <path fill="#0F354E" d="M 18.812 11.968 L 18.445 11.968 L 18.445 13.382 L 18.242 13.382 L 18.242 11.968 L 18.019 11.968 L 18.019 11.796 L 18.242 11.796 L 18.242 11.687 C 18.242 11.515 18.285 11.386 18.371 11.308 C 18.460 11.226 18.601 11.187 18.800 11.187 L 18.800 11.359 C 18.667 11.359 18.578 11.382 18.523 11.433 C 18.468 11.484 18.445 11.570 18.445 11.687 L 18.445 11.796 L 18.812 11.796 Z M 18.812 11.968"/> <path fill="#0F354E" d="M 20.921 12.253 C 20.871 12.156 20.800 12.082 20.710 12.031 C 20.621 11.976 20.519 11.953 20.414 11.953 C 20.300 11.953 20.199 11.976 20.109 12.027 C 20.023 12.078 19.953 12.152 19.902 12.246 C 19.847 12.339 19.824 12.457 19.824 12.585 C 19.824 12.718 19.847 12.832 19.902 12.929 C 19.953 13.027 20.023 13.101 20.109 13.152 C 20.199 13.203 20.300 13.226 20.414 13.226 C 20.519 13.226 20.621 13.203 20.710 13.148 C 20.800 13.097 20.871 13.023 20.921 12.925 C 20.976 12.828 21 12.718 21 12.589 C 21 12.460 20.976 12.351 20.921 12.253 M 19.714 12.160 C 19.781 12.035 19.871 11.941 19.984 11.875 C 20.101 11.808 20.230 11.773 20.375 11.773 C 20.527 11.773 20.656 11.808 20.765 11.878 C 20.875 11.949 20.953 12.039 21 12.144 L 21 11.796 L 21.203 11.796 L 21.203 13.382 L 21 13.382 L 21 13.031 C 20.953 13.140 20.871 13.230 20.761 13.300 C 20.652 13.371 20.523 13.406 20.371 13.406 C 20.230 13.406 20.101 13.371 19.984 13.304 C 19.871 13.234 19.781 13.140 19.714 13.015 C 19.648 12.894 19.617 12.75 19.617 12.585 C 19.617 12.425 19.648 12.281 19.714 12.160"/> <path fill="#0F354E" d="M 22.656 11.851 C 22.75 11.796 22.859 11.769 22.996 11.769 L 22.996 11.980 L 22.937 11.980 C 22.792 11.980 22.675 12.019 22.585 12.097 C 22.496 12.179 22.453 12.312 22.453 12.496 L 22.453 13.382 L 22.25 13.382 L 22.25 11.796 L 22.453 11.796 L 22.453 12.078 C 22.496 11.980 22.566 11.902 22.656 11.851"/> <path fill="#0F354E" d="M 25.035 12.199 C 24.984 12.117 24.917 12.054 24.832 12.011 C 24.75 11.968 24.656 11.949 24.554 11.949 C 24.402 11.949 24.277 11.996 24.167 12.093 C 24.062 12.191 24.003 12.328 23.992 12.503 L 25.105 12.503 C 25.109 12.386 25.085 12.285 25.035 12.199 M 25.304 12.667 L 23.992 12.667 C 24 12.789 24.027 12.890 24.078 12.976 C 24.132 13.058 24.199 13.125 24.285 13.167 C 24.371 13.210 24.464 13.234 24.562 13.234 C 24.695 13.234 24.804 13.203 24.894 13.136 C 24.984 13.074 25.046 12.988 25.074 12.878 L 25.289 12.878 C 25.25 13.035 25.167 13.160 25.039 13.257 C 24.914 13.355 24.753 13.406 24.562 13.406 C 24.414 13.406 24.281 13.371 24.164 13.304 C 24.046 13.238 23.953 13.144 23.886 13.019 C 23.820 12.898 23.785 12.753 23.785 12.589 C 23.785 12.425 23.820 12.281 23.882 12.156 C 23.949 12.035 24.042 11.937 24.160 11.875 C 24.277 11.808 24.414 11.773 24.562 11.773 C 24.714 11.773 24.847 11.808 24.960 11.875 C 25.074 11.937 25.160 12.027 25.222 12.140 C 25.281 12.25 25.312 12.375 25.312 12.507 C 25.312 12.578 25.308 12.632 25.304 12.667"/> <path fill="#DBBF24" d="M 3.636 17.355 L 2.695 17.355 L 2.695 10.355 L 3.636 10.355 C 3.636 13.683 6.097 16.390 9.121 16.390 L 9.121 17.335 C 6.804 17.335 4.769 15.996 3.636 13.996 Z M 3.636 17.355"/> <path fill="#0F354E" d="M 4.027 10.351 L 4.816 10.351 C 4.875 12.433 5.800 14.835 7.398 15.687 C 4.984 14.546 4.113 12.503 4.027 10.351"/> <path fill="#0F354E" d="M 9.117 11.347 L 8.539 11.347 C 8.332 11.347 8.167 11.511 8.167 11.714 L 8.167 14.492 C 7.566 14.445 6.242 12.667 6.242 10.359 L 5.214 10.359 C 5.214 12.714 6.535 15.652 9.121 15.988 Z M 9.117 11.347"/> <path fill="#DBBF24" d="M 8.464 11.765 C 8.515 11.765 8.558 11.722 8.558 11.667 C 8.558 11.617 8.515 11.574 8.464 11.574 C 8.410 11.574 8.367 11.617 8.367 11.667 C 8.367 11.722 8.410 11.765 8.464 11.765"/> </svg>  </a>

  <div class="card @yield('card-class')">
    @yield('content')
  </div>

  <footer class="footer">
    <p class="name">Welfare Nest S.r.l. Società benefit</p>
    <p>Via Nomentana 150, 00162 Roma<br>P.IVA 16633731001</p>
  </footer>
</main>
@stack('scripts')
</body>
</html>
