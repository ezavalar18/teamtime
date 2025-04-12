Pasos para clonar un proyecto de GitHub en Linux y Windows
🔧 Requisitos previos
- Tener git instalado en ambos sistemas.
- Contar con el enlace del repositorio GitHub (ej. https://github.com/usuario/repositorio.git).
  
🐧 En Linux
1. Abrir terminal.
1.2. Verificar git:
   git --version
   Si no está instalado:
   sudo apt update && sudo apt install git
2.3. Ir al directorio deseado:
   cd ~/Documentos
3.4. Clonar el repositorio:
   git clone https://github.com/usuario/repositorio.git
4.5. Ingresar al proyecto:
   cd repositorio
5.6. Configurar saltos de línea:
   git config --global core.autocrlf input

--------------------------------------------
   
🪟 En Windows
1. Abrir Git Bash (recomendado).
6.2. Verificar git:
   git --version
7.3. Ir al directorio deseado:
   cd ~/Downloads
8.4. Clonar el repositorio:
   git clone https://github.com/usuario/repositorio.git
9.5. Ingresar al proyecto:
   cd repositorio
10.6. Configurar saltos de línea:
   git config --global core.autocrlf true
🧠 Buenas prácticas generales
- Antes de trabajar:
  git pull origin main  # o master, según tu rama
- Para subir cambios:
  git add .
  git commit -m "Descripción clara del cambio"
  git push origin main  # o master
