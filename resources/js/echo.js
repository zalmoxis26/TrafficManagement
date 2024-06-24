import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    encrypted: true,
    forceTLS: true
});

// CHANEL DE PEDIMENTO STATUS

window.Echo.channel('trafico-status')
    .listen('MxDocsStatusUpdated', (e) => {

       // FILTRAR POR EMPRESAS
        if (window.empresasDelUsuario.includes(e.trafico.empresa_id)) {
        // Encuentra el elemento td correspondiente al MxDocs actualizado
        const td = document.querySelector(`#trafico-${e.trafico.id}-mx-docs`);

        // Actualiza el contenido del td con el nuevo valor de MxDocs
        if (td) {
            let mxDocsValue = e.trafico.MxDocs;
            if (mxDocsValue === "9") {
                mxDocsValue = "DESADUANAMIENTO LIBRE(VERDE)";
            } else if (mxDocsValue === "11") {
                mxDocsValue = "RECONOCIMIENTO CONCLUIDO";
            }
            td.textContent = mxDocsValue; 
        }


        // Mostrar un toast de actualización
            const toastContainer = document.getElementById('toast-container');

             // Verifica si ya hay 10 toasts en el contenedor
         const toasts = toastContainer.getElementsByClassName('toast');
         if (toasts.length >= 7) {
             // Elimina el toast más antiguo (primer hijo del contenedor)
             toastContainer.removeChild(toasts[0]);
         }


            const toastEl = document.createElement('div');
            toastEl.className = 'toast';
            toastEl.setAttribute('data-autohide', 'true');
            toastEl.innerHTML = `
                <div class="toast-header">
                    <strong class="mr-auto">Estatus de Pedimento Actualizado</strong>
                    <button type="button" class="ml-2 mb-1 close" data-bs-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="toast-body">
                    El Estatus de Pedimento ha sido actualizado. 
                    <a href="/pedimentos/edit/Trafico/${e.trafico.id}/${e.trafico.pedimento_id}" class="btn btn-primary btn-sm">Ir Pedimento</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="toast">Cancelar</button>
                </div>
            `;
            toastContainer.appendChild(toastEl);

             // Inicializar el toast con opciones de autohide y delay
         const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 15000 // 15 segundos
        });
        toast.show();    

         // Eliminar el toast del DOM cuando se oculta
         toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });

        // ACTUALIZAMOS EL SELECT

         const select = document.getElementById('statusPedimento');
         if (select) {
             select.value = e.trafico.MxDocs;
 
             // Opcional: Si quieres actualizar el texto visible del select
             const options = select.options;
             for (let i = 0; i < options.length; i++) {
                 if (options[i].value == e.trafico.MxDocs) {
                     options[i].selected = true;
                     break;
                 }
             }

            

             // Mostrar mensaje de actualización
            const updateMessage = document.getElementById('updateMessage');
            if (updateMessage) {
                updateMessage.textContent = "El Estatus de Pedimento ha sido actualizado";

                 // Ocultar el mensaje después de 10 segundos
                 setTimeout(() => {
                    updateMessage.textContent = "";
                }, 10000); // 10000 milisegundos = 10 segundos
            }
         }

        }
    });

//CHANEL DE STATUS DE REVISION

window.Echo.channel('trafico-revision')
    .listen('RevisionUpdated', (e) => {

        if (window.empresasDelUsuario.includes(e.trafico.empresa_id)) {

        const trafico = e.trafico;

       
        // Encuentra el elemento <td> correspondiente y actualiza su contenido
        const td = document.querySelector(`#trafico-${trafico.id}-revision`);
        if (td) {
            td.innerHTML = `<a href="/revisiones/${trafico.revision_id}/edit">${trafico.Revision}</a>`;
        }

        // Opcionalmente, puedes actualizar un <select> si es necesario
        const select = document.getElementById('statusRevision');
        if (select) {
            // Actualiza el valor seleccionado del select
            select.value = trafico.Revision;

            // Recorre todas las opciones y marca la correspondiente como seleccionada
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value == trafico.Revision) {
                    select.options[i].selected = true;
                    break;
                }
            }
            // Mostrar mensaje de actualización
            const updateMessage = document.getElementById('updateMessage');
            if (updateMessage) {
                updateMessage.textContent = "El Estatus de Revision ha sido actualizado";

                 // Ocultar el mensaje después de 10 segundos
                 setTimeout(() => {
                    updateMessage.textContent = "";
                }, 10000); // 10000 milisegundos = 10 segundos
            }
        }

        
        // TOAST 
        const toastContainer = document.getElementById('toast-container');

         // Verifica si ya hay 10 toasts en el contenedor
         const toasts = toastContainer.getElementsByClassName('toast');
         if (toasts.length >= 7) {
             // Elimina el toast más antiguo (primer hijo del contenedor)
             toastContainer.removeChild(toasts[0]);
         }


        const toastEl = document.createElement('div');
        toastEl.className = 'toast';
        toastEl.setAttribute('data-autohide', 'true');
        toastEl.innerHTML = `
            <div class="toast-header">
                <strong class="mr-auto">Estatus de Revisión Actualizado</strong>
                <button type="button" class="ml-2 mb-1 close" data-bs-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="toast-body">
                El Estatus de Revisión ha sido actualizado. 
                <a href="/revisiones/${trafico.revision_id}/edit" class="btn btn-primary btn-sm">Ir a Revision</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="toast">Cancelar</button>
            </div>
        `;
        toastContainer.appendChild(toastEl);

         // Inicializar el toast con opciones de autohide y delay
         const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 15000 // 15 segundos
        });
        toast.show();    

         // Eliminar el toast del DOM cuando se oculta
         toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });

    }

    });

//CHANEL NUEVO TRAFICO




window.Echo.channel('New-trafico')
    .listen('TraficoCreated', (e) => {

        if (window.empresasDelUsuario.includes(e.trafico.empresa_id)) {

        //AGREGAR EL REGISTRO

        // Verificar si el registro ya existe en el DOM y si estamos en index
        if (window.location.href === '/traficos' && !document.getElementById(`trafico-${e.trafico.id}`)) {
            // Crear el nuevo elemento de la tabla
            const newRow = document.createElement('tr');
            newRow.id = `trafico-${e.trafico.id}`;
            newRow.innerHTML = `
                <td>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="trafico_ids" value="${e.trafico.id}">
                        <label class="form-check-label" for="trafico_${e.trafico.id}">
                            ${e.trafico.id}
                        </label>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-dark btn-sm fs-5" data-bs-toggle="modal" data-bs-target="#createAnexoModal" data-trafico-id="${e.trafico.id}">
                        <i class="bi bi-paperclip"></i>
                    </button>
                </td>
                <td>${e.trafico.embarque ? e.trafico.embarque : ''}</td>
                <td>${e.trafico.folioTransporte ? e.trafico.folioTransporte : ''}</td>
                <td>${e.trafico.fechaReg ? e.trafico.fechaReg : ''}</td>
                <td>${e.trafico.Toperacion ? e.trafico.Toperacion : ''}</td>
                <td>${e.trafico.empresa ? (e.trafico.empresa.descripcion ? e.trafico.empresa.descripcion : '') : ''}</td>
                <td>
                    ${e.trafico.adjuntoFactura ? `
                        <a class="d-block mb-1" href="/facturas/${e.trafico.id}?v=${Date.now()}" target="_blank">${e.trafico.factura ? e.trafico.factura : ''}</a>
                        <button type="button" class="btn btn-secondary btn-sm fs-6" data-bs-toggle="modal" title="Sustitur factura" data-bs-target="#sustituirFacturaModal" data-trafico-id="${e.trafico.id}">
                            <i class="bi bi-recycle"></i>
                        </button>
                    ` : `
                        ${e.trafico.factura ? e.trafico.factura : ''}
                        <button type="button" class="btn btn-secondary btn-sm fs-6" data-bs-toggle="modal" title="Sustitur factura" data-bs-target="#sustituirFacturaModal" data-trafico-id="${e.trafico.id}">
                            <i class="bi bi-recycle"></i>
                        </button>
                    `}
                </td>
                <td>
                    ${e.trafico.pedimento ? `
                        <div class="d-flex p-1">
                            ${e.trafico.pedimento.numPedimento ? e.trafico.pedimento.numPedimento : ''}
                            <a style="text-decoration: none;" href="/pedimentoEditFromTrafico/${e.trafico.id}/${e.trafico.pedimento_id}" class="text-dark fs-4" title="Editar">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            ${e.trafico.pedimento.adjunto ? `
                                <a href="/pedimentos/${e.trafico.pedimento.id}" target="_blank" class="text-success fs-4" title="Ver documento">
                                    <i class="bi bi-file-earmark-ppt-fill"></i>
                                </a>
                            ` : `
                                <a href="#" class="text-danger fs-4" title="SIN PEDIMENTO ADJUNTO">
                                    <i class="bi bi-file-earmark-ppt"></i>
                                </a>
                            `}
                        </div>
                    ` : `
                        <a style="text-decoration: none;" href="/pedimentoCreateFromTrafico/${e.trafico.id}" class="text-dark fs-4" title="Editar${e.trafico.id}">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                    `}
                </td>
                <td>${e.trafico.clavePed ? e.trafico.clavePed : ''}</td>
                <td id="trafico-${e.trafico.id}-mx-docs">
                    ${e.trafico.MxDocs === "9" ? "DESADUANAMIENTO LIBRE(VERDE)" : e.trafico.MxDocs === "11" ? "RECONOCIMIENTO CONCLUIDO" : (e.trafico.MxDocs ? e.trafico.MxDocs : '')}
                </td>
                <td id="trafico-${e.trafico.id}-revision">
                    ${e.trafico.revision_id ? `<a href="/revisiones/${e.trafico.revision_id}">${e.trafico.Revision ? e.trafico.Revision : ''}</a>` : (e.trafico.Revision ? e.trafico.Revision : '')}
                </td>
                <td>${e.trafico.Transporte ? e.trafico.Transporte : ''}</td>
                <td>${e.trafico.Clasificacion ? e.trafico.Clasificacion : ''}</td>
                <td>${e.trafico.Odt ? e.trafico.Odt : ''}</td>
                <td>
                    <form action="/traficos/${e.trafico.id}" method="POST">
                        <a class="btn btn-sm btn-primary fs-6 me-1 mt-1" href="/traficos/${e.trafico.id}"><i class="bi bi-eye"></i>Ver</a>
                        <a class="btn btn-sm btn-success fs-6 mt-1" href="/traficos/${e.trafico.id}/edit"><i class="bi bi-pencil-square"></i>Edit</a>
                    </form>
                </td>
            `;
            document.querySelector('#table-traficos tbody').appendChild(newRow);
        }

 //MOSTRAR UN TOAST

        

            const toastContainer = document.getElementById('toast-container');

             // Verifica si ya hay 10 toasts en el contenedor
         const toasts = toastContainer.getElementsByClassName('toast');
         if (toasts.length >= 7) {
             // Elimina el toast más antiguo (primer hijo del contenedor)
             toastContainer.removeChild(toasts[0]);
         }

            const toastEl = document.createElement('div');
            toastEl.className = 'toast';
            toastEl.setAttribute('data-autohide', 'true');
            toastEl.setAttribute('role', 'alert');
            toastEl.innerHTML = `
            <div class="toast-header">
                <strong class="mr-auto">Nuevo Tráfico</strong>
                <button type="button" class="ml-2 mb-1 close" data-bs-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="toast-body">
                ¿Ir A Traficos?
                <a href="/traficos" class="btn btn-primary btn-sm">Ir</a>
                <a  data-bs-dismiss="toast"  class="btn btn-secondary btn-sm">Cancelar</a>
            </div>
        `;

        toastContainer.appendChild(toastEl);
        
       
         // Inicializar el toast con opciones de autohide y delay
         const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 15000 // 15 segundos
        });
        toast.show();    

         // Eliminar el toast del DOM cuando se oculta
         toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });

        } 
    });


   

window.Echo.channel('comentarios-trafico')
    .listen('NuevoComentario', (event) => {


    if (window.empresasDelUsuario.includes(event.comentario.trafico.empresa_id)) {
        const traficoUrl = "/traficos/" + event.comentario.trafico_id;
        // Verificar si la URL actual no coincide con la URL del tráfico del comentario
    if (window.location.pathname === traficoUrl) { 

        const commentId = event.comentario.id; // Obtener el ID del nuevo comentario
       
            const formattedDate = new Date(event.comentario.created_at).toLocaleString();
            // Agregar el nuevo comentario al elemento #comments

             // Agregar el nuevo comentario al elemento #comments
            const commentsContainer = document.getElementById('comments');

            // Verificar si existe un mensaje de "SIN COMENTARIOS"
            const noCommentsMessage = commentsContainer.querySelector('.no-comments-message');
            if (noCommentsMessage) {
                // Si existe el mensaje, vaciar el contenedor antes de agregar un nuevo comentario
                commentsContainer.innerHTML = '';
            }
 
            const newCommentDiv = document.createElement('div');
            newCommentDiv.className = 'comment';
            newCommentDiv.innerHTML = `
                <strong style="color:cornflowerblue;">[${event.user_name}] ${formattedDate}:</strong> ${event.comentario.content}
            `;
            commentsContainer.appendChild(newCommentDiv);

           

        }
   
        // Verificar si la URL actual no coincide con la URL del tráfico del comentario
    if (window.location.pathname !== traficoUrl) { 

        const toastContainer = document.getElementById('toast-container');

         // Verifica si ya hay 10 toasts en el contenedor
         const toasts = toastContainer.getElementsByClassName('toast');
         if (toasts.length >= 7) {
             // Elimina el toast más antiguo (primer hijo del contenedor)
             toastContainer.removeChild(toasts[0]);
         }

        const toastEl = document.createElement('div');
        toastEl.className = 'toast';
        toastEl.setAttribute('data-autohide', 'true');
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="toast-header bg-dark text-white">
                <strong class="mr-auto">Nuevo Comentario para Tráfico #${event.comentario.trafico_id}</strong>
                <button type="button"  class="btn-close bg-light" data-bs-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true" class=" bg-light text-white"></span>
                </button>
            </div>
            <div class="toast-body">
                ¿Ir a Tráfico?
                <a href="/traficos/${event.comentario.trafico_id}" class="btn btn-primary btn-sm">Ir</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="toast">Cancelar</button>
            </div>
        `;

        toastContainer.appendChild(toastEl);

         // Inicializar el toast con opciones de autohide y delay
         const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 15000 // 15 segundos
        });
        toast.show();

        // Eliminar el toast del DOM cuando se oculta
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
         }
        }
    });




 

window.Echo.channel('comentarios-embarque')
    .listen('NuevoComentarioEmbarque', (event) => {

    
        if (window.empresasDelUsuario.includes(event.embarque.traficos[0].empresa_id)) {
    
        const embarqueUrl = "/embarques/" + event.comentario.embarque_id + "/edit";
        // Verificar si la URL actual no coincide con la URL del tráfico del comentario
    if (window.location.pathname === embarqueUrl) { 

        const commentId = event.comentario.id; // Obtener el ID del nuevo comentario
     
            const formattedDate = new Date(event.comentario.created_at).toLocaleString();
            // Agregar el nuevo comentario al elemento #comments

             // Agregar el nuevo comentario al elemento #comments
            const commentsContainer = document.getElementById('comments');

            // Verificar si existe un mensaje de "SIN COMENTARIOS"
            const noCommentsMessage = commentsContainer.querySelector('.no-comments-message');
            if (noCommentsMessage) {
                // Si existe el mensaje, vaciar el contenedor antes de agregar un nuevo comentario
                commentsContainer.innerHTML = '';
            }
 
            const newCommentDiv = document.createElement('div');
            newCommentDiv.className = 'comment';
            newCommentDiv.innerHTML = `
                <strong style="color:cornflowerblue;">[${event.user_name}] ${formattedDate}:</strong> ${event.comentario.content}
            `;
            commentsContainer.appendChild(newCommentDiv);



        }
   
   
        // Verificar si la URL actual no coincide con la URL del tráfico del comentario
    if (window.location.pathname !== embarqueUrl) { 

        const toastContainer = document.getElementById('toast-container');
         // Verifica si ya hay 10 toasts en el contenedor
         const toasts = toastContainer.getElementsByClassName('toast');
         if (toasts.length >= 7) {
             // Elimina el toast más antiguo (primer hijo del contenedor)
             toastContainer.removeChild(toasts[0]);
         }

        const toastEl = document.createElement('div');
        toastEl.className = 'toast';
        toastEl.setAttribute('data-autohide', 'true');
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="toast-header bg-dark text-white">
                <strong class="mr-auto">Nvo Comentario - Embarque#${event.embarque.numEmbarque}</strong>
                <button type="button"  class="btn-close bg-light" data-bs-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true" class=" bg-light text-white"></span>
                </button>
            </div>
            <div class="toast-body">
                ¿Ir al Embarque?
                <a href="/embarques/${event.comentario.embarque_id}/edit" class="btn btn-primary btn-sm">Ir</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="toast">Cancelar</button>
            </div>
        `;

        toastContainer.appendChild(toastEl);

         // Inicializar el toast con opciones de autohide y delay
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 15000 // 15 segundos
            });
            toast.show();

        // Eliminar el toast del DOM cuando se oculta
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
         }
        }
    });

//CHANNEL SUSTITUIR FACTURA 
window.Echo.channel('cambio-factura')
.listen('FacturaUpdated', (e) => {

    if (window.empresasDelUsuario.includes(e.trafico.empresa_id)) {

    const trafico = e.trafico;

        //actualizamos solo si el trafico no tiene ya el status de corrreciones
        if(e.trafico.Revision != "CORRECCIONES"){

                 // Encuentra el elemento <td> correspondiente y actualiza su contenido
            const td = document.querySelector(`#trafico-${trafico.id}-revision`);

            if (td) {
                td.innerHTML = `<a href="/revisiones/${trafico.revision_id}/edit">${trafico.Revision}</a>`;
            }

            // Opcionalmente, puedes actualizar un <select> si es necesario
            const select = document.getElementById('statusRevision');
            if (select) {
                // Actualiza el valor seleccionado del select
                select.value = trafico.Revision;

                // Recorre todas las opciones y marca la correspondiente como seleccionada
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value == trafico.Revision) {
                        select.options[i].selected = true;
                        break;
                    }
                }
                // Mostrar mensaje de actualización
                const updateMessage = document.getElementById('updateMessage');
                if (updateMessage) {
                    updateMessage.textContent = "El Estatus de Revision ha sido actualizado";

                    // Ocultar el mensaje después de 10 segundos
                    setTimeout(() => {
                        updateMessage.textContent = "";
                    }, 10000); // 10000 milisegundos = 10 segundos
                }
            }
        }
        
        
        // TOAST 
        const toastContainer = document.getElementById('toast-container');

        // Verifica si ya hay 10 toasts en el contenedor
        const toasts = toastContainer.getElementsByClassName('toast');
        if (toasts.length >= 7) {
            // Elimina el toast más antiguo (primer hijo del contenedor)
            toastContainer.removeChild(toasts[0]);
        }


        const toastEl = document.createElement('div');
        toastEl.className = 'toast';
        toastEl.setAttribute('data-autohide', 'true');
        toastEl.innerHTML = `
            <div class="toast-header">
                <strong class="mr-auto">SE HA SUSTITUIDO LA FACTURA</strong>
                <button type="button" class="ml-2 mb-1 close" data-bs-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="toast-body">
                El Estatus de Revisión ha sido actualizado. 
                <a href="/traficos/${trafico.id}" class="btn btn-primary btn-sm">Ir a Anexos</a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="toast">Cancelar</button>
            </div>
        `;
        toastContainer.appendChild(toastEl);

        // Inicializar el toast con opciones de autohide y delay
        const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 15000 // 15 segundos
        });
        toast.show();    

        // Eliminar el toast del DOM cuando se oculta
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
});



