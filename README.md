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
   
1.3. Ir al directorio deseado:
   cd ~/Documentos
   
1.4. Clonar el repositorio:
   git clone https://github.com/usuario/repositorio.git
   
1.5. Ingresar al proyecto:
   cd repositorio
   
--------------------------------------------
   
🪟 En Windows
2. Abrir Git Bash (recomendado).
   
2.1. Verificar git:
   git --version
   
2.2. Ir al directorio deseado:
   cd ~/Downloads
   
2.3. Clonar el repositorio:
   git clone https://github.com/usuario/repositorio.git
   
2.4. Ingresar al proyecto:
   cd repositorio
   
2.5. Configurar saltos de línea:
   git config --global core.autocrlf true
🧠 Buenas prácticas generales
- Antes de trabajar:
  git pull origin main  # o master, según tu rama
- Para subir cambios:
  git add .
  git commit -m "Descripción clara del cambio"
  git push origin main  # o master
