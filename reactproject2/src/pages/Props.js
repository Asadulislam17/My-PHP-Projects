import React from 'react'
import Header from '../components/Header';
import Nav from '../components/Nav';
import Footer from '../components/Footer';

export default function props() {
    const person={name:"Asadul Islam", age:26, address:"Basabo"}
    const { name, age, address } = person;
  return (
    <>
        <Header />
        <Nav />
        <h1>props</h1>
        <p>
            Name:{person.name}<br/>
            Age:{person.age}<br/>
            Address:{person.address}
        </p>
        <h2>React Destructuring Props</h2>
        <p>
            Name:{name}<br/>
            Age:{age}<br/>
            Address:{address}
        </p>

        <Footer />
    </>
    
  )
}
