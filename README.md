Pasos para clonar un proyecto de GitHub en Linux y Windows

🐧 En Linux

1. Abrir terminal.
   
1.2. Verificar git:

   git --version
   Si no está instalado:
   sudo apt update && sudo apt install git
   
1.3. Ir al directorio deseado:
   cd ~/Documentos
   
1.4. Clonar el repositorio:
   git clone git clone https://github.com/ezavalar18/teamtime.git
   
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
   git clone https://github.com/usuario/repositorio.git](https://github.com/ezavalar18/teamtime.git
   
2.4. Ingresar al proyecto:
   cd repositorio
   
🧠 Buenas prácticas generales
- Antes de trabajar:
  git pull origin main  # o master, según tu rama
- Para subir cambios:
  git add .
  git commit -m "Descripción clara del cambio"
  git push origin main  # o master
