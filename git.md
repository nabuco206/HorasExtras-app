Comandos de inicialización y estado
git init: Inicializa un nuevo repositorio de Git en el directorio actual. 
git status: Muestra el estado del repositorio, incluyendo los archivos modificados, preparados y sin seguimiento. 


Comandos para gestionar cambios
git add <archivo>`: Agrega archivos al área de preparación (staging). Para agregar todos los cambios, puedes usar git add .. 
**** git commit -m \"mensaje\"`: Guarda los cambios preparados en el repositorio con un mensaje descriptivo. 
git commit --amend: Permite modificar el último commit realizado. 


Comandos de historial y versiones
git log: Muestra el historial de commits del repositorio. 
git diff: Muestra las diferencias entre el estado de trabajo y el último commit. 


Comandos de ramas
git branch: Lista, crea o elimina ramas. git branch -d <nombre-rama elimina una rama local. 
git checkout <nombre-rama: Cambia a otra rama. 
git checkout -b <nombre-nueva-rama: Crea una nueva rama y cambia a ella en un solo paso. 
git merge <nombre-rama: Fusiona la rama especificada en la rama actual. 


Comandos de repositorios remotos
git clone <url: Crea una copia local de un repositorio remoto. 
git remote add origin <url: Añade un repositorio remoto a tu proyecto. 
*** git push origin <nombre-rama: Envía tus commits locales al repositorio remoto. 
*** git pull origin <nombre-rama: Descarga y fusiona los cambios del repositorio remoto al repositorio local. 
git fetch: Descarga los cambios de un repositorio remoto, pero sin fusionarlos automáticamente. 
