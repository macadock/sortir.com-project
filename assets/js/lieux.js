<script>
    const apiUrl = '/api/';
    const idLieuFormulaire = 'sortie_lieu';
    const idBoutonAjoutLieu = 'ajouter_lieu';
    const idFormClose = 'form_close';
    const idVilleForm = 'sortie_ville';
    const idModaleAjoutLieu = 'form_ajouter_lieu';
    const idSelectNouvelleVille = 'select_nouvelle_ville';
    const idFormAjoutLieu = 'ajout_lieu';

    window.onload = supprimerLieux(idLieuFormulaire);

    const ajoutLieu = document.getElementById(idBoutonAjoutLieu);
    ajoutLieu.addEventListener('click', afficherFormAjoutLieu);

    const fermerModale = document.getElementById(idFormClose);
    fermerModale.addEventListener('click', masquerFormAjoutLieu);

    const ville = document.getElementById(idVilleForm);
    ville.addEventListener('change', chargerLieux);

    const lieu = document.getElementById(idLieuFormulaire);
    lieu.addEventListener('change', chargerInfosLieu);

    const formModalAjoutLieu = document.getElementById(idModaleAjoutLieu);

    const formAjoutLieu = document.getElementById(idFormAjoutLieu);

    const form = document.forms[idFormAjoutLieu];

    let idLieu = null;

    formAjoutLieu.addEventListener('submit', function (event) {
    event.preventDefault();
    const data = new URLSearchParams();
    data.append('nom', form.elements["nom"].value);
    data.append('rue', form.elements["rue"].value);
    data.append('ville', form.elements["ville"].value);
    data.append('latitude', form.elements["latitude"].value);
    data.append('longitude', form.elements["longitude"].value);

    const idVille = form.elements["ville"].value;

    fetch(apiUrl+'lieu/create', {
    method: 'POST',
    body: data,
})
    .then(response => response.json())
    .then(lieuCree => {
    masquerFormAjoutLieu();
    ville.value = idVille;
    idLieu = lieuCree.id;
    chargerLieux();
})
});

    function afficherFormAjoutLieu() {
    chargerVilles()
    formModalAjoutLieu.classList.remove('hidden');
    formModalAjoutLieu.classList.add('block');
}

    function masquerFormAjoutLieu() {
    formModalAjoutLieu.classList.remove('block');
    formModalAjoutLieu.classList.add('hidden');
    form.reset();
}

    function chargerVilles() {
    fetch(apiUrl+'villes')
        .then(response => response.json())
        .then(villes => {
            supprimerLieux(idSelectNouvelleVille);

            for (const ville of villes) {

                const option = document.createElement('option');
                option.textContent = ville.nom;
                option.value = ville.id;
                document.getElementById(idSelectNouvelleVille).appendChild(option);
            }
            if (idLieu != null) {
                lieu.value = idLieu;
                idLieu = null;
            }

        })
}


    function supprimerLieux(selectToRemoveChild) {
    let nbLieux = document.getElementById(selectToRemoveChild).childElementCount;
    for (let i = 0; i < nbLieux - 1; i++) {
    document.getElementById(selectToRemoveChild).lastElementChild.remove();
}
}

    function chargerLieux() {

    fetch(apiUrl+ville.value+'/lieux')
        .then(response => response.json())
        .then(lieux => {
            supprimerLieux(idLieuFormulaire);

            for (const lieu of lieux) {
                document.getElementById('rue').textContent = '';
                document.getElementById('latitude').textContent = '';
                document.getElementById('longitude').textContent = '';

                const option = document.createElement('option');
                option.textContent = lieu.nom;
                option.value = lieu.id;
                document.getElementById(idLieuFormulaire).appendChild(option);
            }
        } )
}

    function chargerInfosLieu() {
    const idLieu = lieu.value;

    fetch(apiUrl+'lieu/infos/'+idLieu)
    .then(response => response.json())
    .then(infosLieu => {
    document.getElementById('rue').textContent = infosLieu.rue;
    document.getElementById('latitude').textContent = infosLieu.latitude;
    document.getElementById('longitude').textContent = infosLieu.longitude;
})
    .catch()

}

</script>

