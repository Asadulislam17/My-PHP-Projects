import React from 'react'
import Header from '../components/Header';
import Nav from '../components/Nav';
import Footer from '../components/Footer';

export default function Lists() {
    const cars = ['Ford', 'BMW', 'Audi'];
  return (
    <>
        <Header />
        <Nav />
        <h1>Lists</h1>
        <h1>My Cars:</h1>
        <ul>
            {cars.map((car) => <li>I am a { car }</li>)}
        </ul>
        <Footer />
    </>
  )
}
