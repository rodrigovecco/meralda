# Welcome to Meralda - mwPHPlib

Meralda is a comprehensive PHP and JavaScript library developed with decades of experience in building information systems. Named after our beloved cat who inspired creativity and companionship, Meralda aims to empower developers by providing a versatile set of tools and utilities for web development.

## About Meralda

Meralda is the culmination of decades of software development expertise, starting from the creation of FaciPub in 2003, a popular CMS, to the evolution of DrSoft, a flexible platform for data gathering and reporting since 2014. Along the way, we encountered challenges, learned valuable lessons, and honed our craft, which eventually led to the birth of Meralda in 2024.

## Key Features

- **Modularity:** Meralda is designed with modularity in mind, allowing developers to pick and choose components based on their project requirements.
- **Flexibility:** Whether you're building a content management system, a data-driven application, or a responsive website, Meralda offers the flexibility to adapt to diverse use cases.
- **Getting Started:** To start using Meralda in your projects, simply clone the repository and follow the instructions in the documentation.
- **Contributing:** We welcome contributions from the community! Whether it's fixing bugs, adding new features, or improving documentation, every contribution is valuable.
- **License:** Meralda is open-source software released under the [MIT License](LICENSE). You are free to use, modify, and distribute Meralda for both commercial and non-commercial purposes.

## 🛠️ Initialize Your App
To start a new application using Meralda:
1. Copy the example application files from `example/demo/app` to the `src/app` directory:
   ```bash
   cp -r example/demo/app src/app
2. Edit the file src/app/cfg/db.php to configure your database connection.
3. Review and adjust other configuration files inside src/app/cfg/ as needed to fit your environment.


## About mwPHPlib (by Rodrigo Vecco Haddad)

mwPHPlib, now known as Meralda, is a collection of PHP classes designed to facilitate the development of complex applications in PHP. These classes and development techniques and methods are the result of over 20 years of experience in this field, providing resources to build robust web platforms with significant time savings. The modular structure allows grouping classes into folders and files that relate to their names and making them available as they are required through automatic loading mechanisms. Typically, an application developed with this platform will have a main object called an application, on which various object handlers will depend, which will be loaded on demand. 

Created by Rodrigo Vecco Haddad (rodrigo@novoingenios.com).

# Meralda

Meralda uses **Git submodules** for managing some parts of the project. To ensure you get the complete repository with all submodules, follow these instructions.

## 🚀 Clone the Repository with Submodules

To properly clone Meralda along with all its submodules, use the following command:

```bash
git clone --recurse-submodules https://github.com/rodrigovecco/meralda.git

If You Already Cloned the Repo (Without Submodules)
```bash
git submodule update --init --recursive
